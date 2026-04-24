<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyBranch;
use App\Models\CompanyContact;
use App\Models\ImportAudit;
use App\Models\MasterData\Extra;
use App\Models\MasterData\FlightChildPricing;
use App\Models\MasterData\FlightPolicy;
use App\Models\MasterData\FlightProvider;
use App\Models\MasterData\FlightRoute;
use App\Models\MasterData\FlightSeasonalRate;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class LegacySqlImportService
{
    private string $importSource = 'unknown';

    private readonly LegacyRowValidator $validator;

    public function __construct()
    {
        $this->validator = new LegacyRowValidator();
    }

    public function importFile(string $filePath, string $companyEmail, bool $dryRun = false): array
    {
        $this->importSource = basename($filePath);

        if (!is_file($filePath)) {
            throw new InvalidArgumentException("Legacy SQL file not found: {$filePath}");
        }

        $sql = file_get_contents($filePath);
        if ($sql === false || trim($sql) === '') {
            throw new InvalidArgumentException('Legacy SQL file is empty or unreadable.');
        }

        $company = Company::where('email', $companyEmail)->first();
        if (!$company) {
            throw new InvalidArgumentException("Company not found by email: {$companyEmail}");
        }

        $summary = [
            'company_id' => $company->id,
            'agent_contacts_users' => 0,
            'branches' => 0,
            'contacts' => 0,
            'extras' => 0,
            'flight_providers' => 0,
            'flight_child_pricing' => 0,
            'flight_routes' => 0,
            'flight_seasonal_rates' => 0,
            'rows_accepted' => 0,
            'rows_rejected' => 0,
            'rows_skipped' => 0,
        ];

        $runner = function () use (&$summary, $sql, $company): void {
            $this->deduplicateFlightProviders($company);

            foreach ($this->extractInsertBatches($sql) as $batch) {
                $table = strtolower($batch['table']);
                $rows  = $batch['rows'];

                if ($table === 'agent_contacts') {
                    $r = $this->importAgentContacts($company, $rows);
                    $summary['agent_contacts_users'] += $r['imported'];
                    $summary['rows_accepted'] += $r['imported'];
                    $summary['rows_rejected'] += $r['rejected'];
                    continue;
                }

                if ($table === 'companydetails') {
                    $this->importCompanyDetails($company, $rows);
                    continue;
                }

                if ($table === 'companybranches') {
                    $r = $this->importCompanyBranches($company, $rows);
                    $summary['branches'] += $r['imported'];
                    $summary['rows_accepted'] += $r['imported'];
                    $summary['rows_rejected'] += $r['rejected'];
                    continue;
                }

                if ($table === 'contacts') {
                    $r = $this->importContacts($company, $rows);
                    $summary['contacts'] += $r['imported'];
                    $summary['rows_accepted'] += $r['imported'];
                    $summary['rows_rejected'] += $r['rejected'];
                    continue;
                }

                if ($table === 'extras') {
                    $r = $this->importExtras($company, $rows);
                    $summary['extras'] += $r['imported'];
                    $summary['rows_accepted'] += $r['imported'];
                    $summary['rows_rejected'] += $r['rejected'];
                    $summary['rows_skipped']  += $r['skipped'];
                    continue;
                }

                if ($table === 'flight_childages') {
                    $r = $this->importFlightChildAges($company, $rows);
                    $summary['flight_child_pricing'] += $r['imported'];
                    $summary['rows_accepted'] += $r['imported'];
                    $summary['rows_rejected'] += $r['rejected'];
                    continue;
                }

                if ($table === 'flight_destinations') {
                    $this->importFlightDestinationsAsPolicies($company, $rows);
                    continue;
                }

                if ($table === 'flight_rates') {
                    $r = $this->importFlightRates($company, $rows);
                    $summary['flight_providers']    += $r['providers'];
                    $summary['flight_routes']        += $r['routes'];
                    $summary['flight_seasonal_rates'] += $r['rates'];
                    $summary['rows_accepted'] += $r['accepted'];
                    $summary['rows_rejected'] += $r['rejected'];
                    $summary['rows_skipped']  += $r['skipped'];
                }
            }
        };

        if ($dryRun) {
            DB::transaction(function () use ($runner): void {
                $runner();
                throw new \RuntimeException('__DRY_RUN_ROLLBACK__');
            });
        } else {
            DB::transaction($runner);
        }

        return $summary;
    }

    private function extractInsertBatches(string $sql): array
    {
        preg_match_all('/insert\s+into\s+`([^`]+)`\s*\(([^)]*)\)\s*values\s*(.*?);/is', $sql, $matches, PREG_SET_ORDER);

        $batches = [];
        foreach ($matches as $match) {
            $table = trim($match[1]);
            $columns = array_map(static fn (string $column) => trim($column, " `\t\n\r\0\x0B"), explode(',', $match[2]));
            $tuples = $this->splitTuples($match[3]);

            $rows = [];
            foreach ($tuples as $tuple) {
                $values = $this->splitCsvValues($tuple);
                if (count($values) !== count($columns)) {
                    continue;
                }
                $rows[] = array_combine($columns, $values);
            }

            $batches[] = ['table' => $table, 'rows' => $rows];
        }

        return $batches;
    }

    private function splitTuples(string $valuesSection): array
    {
        $tuples = [];
        $length = strlen($valuesSection);
        $depth = 0;
        $inString = false;
        $escaped = false;
        $buffer = '';

        for ($i = 0; $i < $length; $i++) {
            $char = $valuesSection[$i];

            if ($inString) {
                $buffer .= $char;
                if ($escaped) {
                    $escaped = false;
                } elseif ($char === '\\') {
                    $escaped = true;
                } elseif ($char === "'") {
                    $inString = false;
                }
                continue;
            }

            if ($char === "'") {
                $inString = true;
                $buffer .= $char;
                continue;
            }

            if ($char === '(') {
                $depth++;
            }

            if ($depth > 0) {
                $buffer .= $char;
            }

            if ($char === ')') {
                $depth--;
                if ($depth === 0) {
                    $trimmed = trim($buffer);
                    if ($trimmed !== '') {
                        $tuples[] = trim($trimmed, "()\t\n\r ");
                    }
                    $buffer = '';
                }
            }
        }

        return $tuples;
    }

    private function splitCsvValues(string $tuple): array
    {
        $values = [];
        $length = strlen($tuple);
        $inString = false;
        $escaped = false;
        $buffer = '';

        for ($i = 0; $i < $length; $i++) {
            $char = $tuple[$i];

            if ($inString) {
                if ($escaped) {
                    $buffer .= $char;
                    $escaped = false;
                    continue;
                }

                if ($char === '\\') {
                    $buffer .= $char;
                    $escaped = true;
                    continue;
                }

                if ($char === "'") {
                    $inString = false;
                    continue;
                }

                $buffer .= $char;
                continue;
            }

            if ($char === "'") {
                $inString = true;
                continue;
            }

            if ($char === ',') {
                $values[] = $this->normalizeSqlValue($buffer);
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        $values[] = $this->normalizeSqlValue($buffer);

        return $values;
    }

    private function normalizeSqlValue(string $raw): mixed
    {
        $value = trim($raw);
        if ($value === '' || strcasecmp($value, 'null') === 0) {
            return null;
        }

        $value = str_replace(["\\r", "\\n", "\\t", "\\\\", "\\'"] , ["\r", "\n", "\t", "\\", "'"] , $value);

        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float) $value : (int) $value;
        }

        return trim($value);
    }

    private function importAgentContacts(Company $company, array $rows): array
    {
        $imported = $rejected = 0;

        foreach ($rows as $idx => $row) {
            $violations = $this->validator->validate('agent_contacts', $row);
            if ($violations !== []) {
                $this->auditRow($company, 'agent_contacts', $idx, 'rejected', $row, $violations);
                $rejected++;
                continue;
            }

            $email = strtolower((string) ($row['email'] ?? ''));

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'company_id' => $company->id,
                    'name' => (string) ($row['fullname'] ?? $email),
                    'password' => 'password',
                    'role' => User::where('email', $email)->value('role') ?? User::ROLE_STAFF,
                    'is_active' => true,
                ]
            );

            $this->auditRow($company, 'agent_contacts', $idx, 'accepted', $row, [], User::class, $user->id);
            $imported++;
        }

        return ['imported' => $imported, 'rejected' => $rejected, 'skipped' => 0];
    }

    private function importCompanyDetails(Company $company, array $rows): void
    {
        $row = Arr::first($rows);
        if (!$row) {
            return;
        }

        $company->update([
            'name' => (string) ($row['companyname'] ?? $company->name),
            'email' => (string) ($row['email'] ?? $company->email),
            'address' => (string) ($row['address'] ?? $company->address),
            'is_active' => true,
        ]);
    }

    private function importCompanyBranches(Company $company, array $rows): array
    {
        $imported = $rejected = 0;
        foreach ($rows as $idx => $row) {
            $violations = $this->validator->validate('companybranches', $row);
            if ($violations !== []) {
                $this->auditRow($company, 'companybranches', $idx, 'rejected', $row, $violations);
                $rejected++;
                continue;
            }

            $name = trim((string) ($row['name'] ?? ''));
            $branch = CompanyBranch::updateOrCreate(
                ['company_id' => $company->id, 'name' => $name],
                [
                    'address' => $row['address'] ?? null,
                    'vrn' => $row['vrn'] ?? null,
                    'tin' => $row['tin'] ?? null,
                    'banking_details' => $row['bankingdetails'] ?? null,
                    'source_code' => $row['cname'] ?? null,
                ]
            );
            $this->auditRow($company, 'companybranches', $idx, 'accepted', $row, [], CompanyBranch::class, $branch->id);
            $imported++;
        }

        return ['imported' => $imported, 'rejected' => $rejected, 'skipped' => 0];
    }

    private function importContacts(Company $company, array $rows): array
    {
        $imported = $rejected = 0;

        foreach ($rows as $idx => $row) {
            $violations = $this->validator->validate('contacts', $row);
            if ($violations !== []) {
                $this->auditRow($company, 'contacts', $idx, 'rejected', $row, $violations);
                $rejected++;
                continue;
            }

            $companyName = trim((string) ($row['companyname'] ?? ''));
            $contact = CompanyContact::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'category' => $row['category'] ?? null,
                    'company_name' => $companyName,
                ],
                [
                    'contact_type' => $row['contacttype'] ?? null,
                    'full_name' => $row['fullname'] ?? null,
                    'email_work' => $row['email_work'] ?? $row['emails'] ?? null,
                    'email_personal' => $row['email_personal'] ?? null,
                    'phone_business' => $row['phone_business'] ?? $row['phones'] ?? null,
                    'phone_mobile' => $row['phone_mobile'] ?? null,
                    'country' => $row['country'] ?? null,
                    'website' => $row['website'] ?? null,
                    'markup' => (float) ($row['markup'] ?? 0),
                    'elements' => $row['elements'] ?? null,
                    'source_code' => $row['cname'] ?? null,
                    'metadata' => [
                        'type' => $row['type'] ?? null,
                        'tin' => $row['TIN'] ?? null,
                        'vrn' => $row['VRN'] ?? null,
                        'notes' => $row['notes'] ?? null,
                    ],
                ]
            );

            $this->auditRow($company, 'contacts', $idx, 'accepted', $row, [], CompanyContact::class, $contact->id);
            $imported++;
        }

        return ['imported' => $imported, 'rejected' => $rejected, 'skipped' => 0];
    }

    private function importExtras(Company $company, array $rows): array
    {
        $byNameAndYear = [];
        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $year = (int) ($row['year'] ?? 0);
            $key = strtolower($name);

            if (!isset($byNameAndYear[$key]) || $year >= (int) ($byNameAndYear[$key]['row']['year'] ?? 0)) {
                $byNameAndYear[$key] = ['row' => $row, 'year' => $year, 'originalKey' => $key];
            }
        }

        $imported = $rejected = $skipped = 0;
        $currentYear = (int) date('Y');

        foreach ($byNameAndYear as $entry) {
            $row = $entry['row'];
            $year = (int) ($row['year'] ?? 0);
            $idx = $entry['originalKey'];

            $violations = $this->validator->validate('extras', $row);
            if ($violations !== []) {
                $this->auditRow($company, 'extras', $idx, 'rejected', $row, $violations);
                $rejected++;
                continue;
            }

            if ($year > 0 && $year < ($currentYear - 1)) {
                $this->auditRow($company, 'extras', $idx, 'skipped', $row, ['Year ' . $year . ' is older than current-1']);
                $skipped++;
                continue;
            }

            foreach ($this->normalizeLegacyExtraRows($row) as $normalized) {
                $extra = Extra::updateOrCreate(
                    ['company_id' => $company->id, 'name' => $normalized['name']],
                    ['price' => $normalized['price']]
                );
                $this->auditRow($company, 'extras', $idx, 'accepted', $row, [], Extra::class, $extra->id);
                $imported++;
            }
        }

        return ['imported' => $imported, 'rejected' => $rejected, 'skipped' => $skipped];
    }

    private function normalizeLegacyExtraRows(array $row): array
    {
        $name = (string) $row['name'];
        $adult = (float) ($row['peradult'] ?? 0);
        $teen = (float) ($row['perteen'] ?? 0);
        $child = (float) ($row['perchild'] ?? 0);
        $vehicle = (float) ($row['pervehicle'] ?? 0);
        $group = (float) ($row['pergroup'] ?? 0);

        $normalized = [];
        $personRates = array_filter([
            'Adult' => $adult,
            'Teen' => $teen,
            'Child' => $child,
        ], static fn (float $value) => $value > 0);

        if ($personRates !== []) {
            $unique = array_values(array_unique(array_values($personRates)));
            if (count($unique) === 1) {
                $normalized[] = ['name' => $name, 'price' => $unique[0]];
            } else {
                foreach ($personRates as $label => $value) {
                    $normalized[] = ['name' => sprintf('%s (%s)', $name, $label), 'price' => $value];
                }
            }
        }

        if ($vehicle > 0) {
            $normalized[] = ['name' => sprintf('%s (Per Vehicle)', $name), 'price' => $vehicle];
        }
        if ($group > 0) {
            $normalized[] = ['name' => sprintf('%s (Per Group)', $name), 'price' => $group];
        }

        return $normalized;
    }

    private function importFlightChildAges(Company $company, array $rows): array
    {
        $imported = $rejected = 0;
        foreach ($rows as $idx => $row) {
            $violations = $this->validator->validate('flight_childages', $row);
            if ($violations !== []) {
                $this->auditRow($company, 'flight_childages', $idx, 'rejected', $row, $violations);
                $rejected++;
                continue;
            }

            $supplier = trim((string) ($row['supplier'] ?? ''));
            [$provider] = $this->resolveFlightProvider($company, $supplier);

            $rateText = strtolower(trim((string) ($row['rate'] ?? '')));
            $pricingType = 'fixed';
            $value = 0;
            if (str_contains($rateText, 'free')) {
                $pricingType = 'free';
                $value = 0;
            } elseif (str_contains($rateText, 'child') || str_contains($rateText, 'adult')) {
                $pricingType = 'percentage';
                $value = 100;
            }

            $pricing = FlightChildPricing::updateOrCreate(
                [
                    'flight_provider_id' => $provider->id,
                    'min_age' => (int) ($row['fromage'] ?? 0),
                    'max_age' => (int) ($row['toage'] ?? 0),
                ],
                [
                    'pricing_type' => $pricingType,
                    'value' => $value,
                ]
            );

            FlightPolicy::updateOrCreate(
                [
                    'flight_provider_id' => $provider->id,
                    'policy_type' => 'general',
                    'title' => 'Legacy child age rule ' . ((string) ($row['year'] ?? '')),
                ],
                [
                    'content' => sprintf(
                        'Ages %s-%s: %s',
                        (string) ($row['fromage'] ?? '0'),
                        (string) ($row['toage'] ?? '0'),
                        (string) ($row['rate'] ?? '')
                    ),
                ]
            );

            $this->auditRow($company, 'flight_childages', $idx, 'accepted', $row, [], FlightChildPricing::class, $pricing->id);
            $imported++;
        }

        return ['imported' => $imported, 'rejected' => $rejected, 'skipped' => 0];
    }

    private function importFlightDestinationsAsPolicies(Company $company, array $rows): void
    {
        $row = Arr::first($rows);
        if (!$row) {
            return;
        }

        $list = trim((string) ($row['name'] ?? ''));
        if ($list === '') {
            return;
        }

        $providers = FlightProvider::where('company_id', $company->id)->get();
        foreach ($providers as $provider) {
            FlightPolicy::updateOrCreate(
                [
                    'flight_provider_id' => $provider->id,
                    'policy_type' => 'general',
                    'title' => 'Legacy supported destinations',
                ],
                ['content' => $list]
            );
        }
    }

    private function importFlightRates(Company $company, array $rows): array
    {
        $providers = $routes = $rates = $accepted = $rejected = $skipped = 0;

        foreach ($rows as $idx => $row) {
            $violations = $this->validator->validate('flight_rates', $row);
            if ($violations !== []) {
                $this->auditRow($company, 'flight_rates', $idx, 'rejected', $row, $violations);
                $rejected++;
                continue;
            }

            $supplier = trim((string) ($row['supplier'] ?? ''));
            $from = trim((string) ($row['frompoint'] ?? ''));
            $to = trim((string) ($row['topoint'] ?? ''));

            [$provider, $created] = $this->resolveFlightProvider($company, $supplier);
            if ($created) {
                $providers++;
            }

            $route = FlightRoute::firstOrCreate(
                [
                    'flight_provider_id' => $provider->id,
                    'origin_name' => $from,
                    'arrival_name' => $to,
                ],
                [
                    'origin_destination_id' => null,
                    'arrival_destination_id' => null,
                    'flight_duration_minutes' => null,
                ]
            );
            if ($route->wasRecentlyCreated) {
                $routes++;
            }

            $year = (int) ($row['year'] ?? date('Y'));
            $seasonName = trim((string) ($row['season'] ?? 'Year Round')) ?: 'Year Round';
            $rateType = strtolower((string) ($row['ratetype'] ?? 'scheduled'));
            if (!in_array($rateType, ['scheduled', 'charter'], true)) {
                $rateType = 'scheduled';
            }

            $rate = FlightSeasonalRate::updateOrCreate(
                [
                    'flight_provider_id' => $provider->id,
                    'flight_route_id' => $route->id,
                    'season_name' => $seasonName . ' ' . $year,
                    'rate_type' => $rateType,
                ],
                [
                    'valid_from' => sprintf('%d-01-01', $year),
                    'valid_to' => sprintf('%d-12-31', $year),
                    'adult_rate' => (float) ($row['adultrate'] ?? 0),
                    'child_rate' => (float) ($row['childrate'] ?? 0),
                    'infant_rate' => 0,
                    'charter_rate' => (float) ($row['guiderate'] ?? 0),
                    'currency' => 'USD',
                ]
            );
            $rates++;

            $this->auditRow($company, 'flight_rates', $idx, 'accepted', $row, [], FlightSeasonalRate::class, $rate->id);
            $accepted++;
        }

        return [
            'providers' => $providers,
            'routes' => $routes,
            'rates' => $rates,
            'accepted' => $accepted,
            'rejected' => $rejected,
            'skipped' => $skipped,
        ];
    }

    private function resolveFlightProvider(Company $company, string $supplier): array
    {
        $normalizedName = ucwords(strtolower(trim($supplier)));

        $provider = FlightProvider::where('company_id', $company->id)
            ->whereRaw('LOWER(name) = ?', [strtolower($normalizedName)])
            ->first();

        if ($provider) {
            return [$provider, false];
        }

        $provider = FlightProvider::create([
            'company_id' => $company->id,
            'name' => $normalizedName,
            'vat_type' => 'inclusive',
            'markup' => 0,
            'is_active' => true,
        ]);

        return [$provider, true];
    }

    private function auditRow(
        Company $company,
        string $sourceTable,
        int|string $rowIndex,
        string $status,
        array $rawRow,
        array $violations = [],
        ?string $targetModel = null,
        int|null $targetId = null
    ): void {
        ImportAudit::create([
            'company_id' => $company->id,
            'import_source' => $this->importSource,
            'source_table' => $sourceTable,
            'source_row_index' => (int) $rowIndex,
            'status' => $status,
            'violations' => $violations ?: null,
            'raw_row' => $rawRow,
            'target_model' => $targetModel,
            'target_id' => $targetId,
        ]);
    }

    private function deduplicateFlightProviders(Company $company): void
    {
        $providers = FlightProvider::where('company_id', $company->id)
            ->orderBy('id')
            ->get()
            ->groupBy(static fn (FlightProvider $provider) => strtolower(trim($provider->name)));

        foreach ($providers as $group) {
            if ($group->count() <= 1) {
                continue;
            }

            /** @var FlightProvider $primary */
            $primary = $group->first();
            foreach ($group->slice(1) as $duplicate) {
                DB::table('flight_routes')->where('flight_provider_id', $duplicate->id)->update(['flight_provider_id' => $primary->id]);
                DB::table('flight_seasonal_rates')->where('flight_provider_id', $duplicate->id)->update(['flight_provider_id' => $primary->id]);
                DB::table('scheduled_flights')->where('flight_provider_id', $duplicate->id)->update(['flight_provider_id' => $primary->id]);
                DB::table('charter_flights')->where('flight_provider_id', $duplicate->id)->update(['flight_provider_id' => $primary->id]);
                DB::table('aircraft_types')->where('flight_provider_id', $duplicate->id)->update(['flight_provider_id' => $primary->id]);
                DB::table('flight_child_pricing')->where('flight_provider_id', $duplicate->id)->update(['flight_provider_id' => $primary->id]);
                DB::table('flight_policies')->where('flight_provider_id', $duplicate->id)->update(['flight_provider_id' => $primary->id]);
                $duplicate->delete();
            }
        }
    }
}

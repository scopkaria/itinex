<?php

namespace App\Services;

/**
 * Validates a single parsed row from a legacy SQL import batch.
 *
 * Each rule is a callable that returns a string error message or null when the
 * row passes.  Rules are defined per source table so the validator stays
 * stateless and easy to extend without touching the importer itself.
 */
class LegacyRowValidator
{
    /**
     * Validate a row from the given legacy source table.
     *
     * Returns an empty array when the row is valid, or a non-empty array of
     * human-readable violation strings when it should be rejected.
     *
     * @param  array<string, mixed>  $row
     * @return string[]
     */
    public function validate(string $sourceTable, array $row): array
    {
        $rules = $this->rulesFor($sourceTable);

        $violations = [];
        foreach ($rules as $rule) {
            $message = $rule($row);
            if ($message !== null) {
                $violations[] = $message;
            }
        }

        return $violations;
    }

    // ─── Rule sets per legacy table ────────────────────────────────────────────

    /**
     * @return array<int, callable(array): string|null>
     */
    private function rulesFor(string $sourceTable): array
    {
        return match ($sourceTable) {
            'flight_rates'     => $this->flightRateRules(),
            'extras'           => $this->extrasRules(),
            'flight_childages' => $this->flightChildAgeRules(),
            'contacts'         => $this->contactsRules(),
            'agent_contacts'   => $this->agentContactRules(),
            'companybranches'  => $this->branchRules(),
            default            => [],  // tolerate tables without explicit rules
        };
    }

    // ─── flight_rates ──────────────────────────────────────────────────────────

    private function flightRateRules(): array
    {
        return [
            // Required string fields
            static function (array $row): ?string {
                if (empty(trim((string) ($row['supplier'] ?? '')))) {
                    return 'supplier is required';
                }
                return null;
            },
            static function (array $row): ?string {
                if (empty(trim((string) ($row['frompoint'] ?? '')))) {
                    return 'frompoint (origin) is required';
                }
                return null;
            },
            static function (array $row): ?string {
                if (empty(trim((string) ($row['topoint'] ?? '')))) {
                    return 'topoint (destination) is required';
                }
                return null;
            },
            // Rate sanity – adult rate must be present and ≥ 0
            static function (array $row): ?string {
                $rate = $row['adultrate'] ?? null;
                if ($rate === null || !is_numeric($rate)) {
                    return 'adultrate must be a number';
                }
                if ((float) $rate < 0) {
                    return sprintf('adultrate is negative (%s)', $rate);
                }
                return null;
            },
            // Child rate must not exceed adult rate (common data-entry mistake)
            static function (array $row): ?string {
                $adult = (float) ($row['adultrate'] ?? 0);
                $child = (float) ($row['childrate'] ?? 0);
                if ($adult > 0 && $child > $adult) {
                    return sprintf(
                        'childrate (%s) exceeds adultrate (%s) – likely data error',
                        $child,
                        $adult
                    );
                }
                return null;
            },
            // Year must be a four-digit positive integer
            static function (array $row): ?string {
                $year = $row['year'] ?? null;
                if ($year === null || !preg_match('/^\d{4}$/', (string) $year)) {
                    return sprintf('year is invalid (%s)', $year);
                }
                return null;
            },
            // Origin and destination must be different
            static function (array $row): ?string {
                $from = strtolower(trim((string) ($row['frompoint'] ?? '')));
                $to   = strtolower(trim((string) ($row['topoint'] ?? '')));
                if ($from !== '' && $to !== '' && $from === $to) {
                    return sprintf("frompoint and topoint are identical ('%s')", $from);
                }
                return null;
            },
        ];
    }

    // ─── extras ───────────────────────────────────────────────────────────────

    private function extrasRules(): array
    {
        return [
            static function (array $row): ?string {
                if (empty(trim((string) ($row['name'] ?? '')))) {
                    return 'extra name is required';
                }
                return null;
            },
            static function (array $row): ?string {
                $allZero = (float) ($row['peradult']   ?? 0) === 0.0
                        && (float) ($row['perteen']    ?? 0) === 0.0
                        && (float) ($row['perchild']   ?? 0) === 0.0
                        && (float) ($row['pervehicle'] ?? 0) === 0.0
                        && (float) ($row['pergroup']   ?? 0) === 0.0;

                if ($allZero) {
                    return 'all price fields are zero – nothing to import';
                }
                return null;
            },
        ];
    }

    // ─── flight_childages ─────────────────────────────────────────────────────

    private function flightChildAgeRules(): array
    {
        return [
            static function (array $row): ?string {
                if (empty(trim((string) ($row['supplier'] ?? '')))) {
                    return 'supplier is required';
                }
                return null;
            },
            static function (array $row): ?string {
                $from = $row['fromage'] ?? null;
                $to   = $row['toage']   ?? null;
                if (!is_numeric($from) || (int) $from < 0) {
                    return sprintf('fromage is invalid (%s)', $from);
                }
                if (!is_numeric($to) || (int) $to < 0) {
                    return sprintf('toage is invalid (%s)', $to);
                }
                return null;
            },
            static function (array $row): ?string {
                $from = (int) ($row['fromage'] ?? 0);
                $to   = (int) ($row['toage']   ?? 0);
                if ($from > $to) {
                    return sprintf('fromage (%d) is greater than toage (%d)', $from, $to);
                }
                return null;
            },
            static function (array $row): ?string {
                if (empty(trim((string) ($row['rate'] ?? '')))) {
                    return 'rate description is required';
                }
                return null;
            },
        ];
    }

    // ─── contacts ─────────────────────────────────────────────────────────────

    private function contactsRules(): array
    {
        return [
            static function (array $row): ?string {
                if (empty(trim((string) ($row['companyname'] ?? '')))) {
                    return 'companyname is required for contacts';
                }
                return null;
            },
        ];
    }

    // ─── agent_contacts ───────────────────────────────────────────────────────

    private function agentContactRules(): array
    {
        return [
            static function (array $row): ?string {
                $email = trim((string) ($row['email'] ?? ''));
                if ($email === '') {
                    return 'email is required for agent contacts';
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return sprintf("email '%s' is not a valid email address", $email);
                }
                return null;
            },
        ];
    }

    // ─── companybranches ──────────────────────────────────────────────────────

    private function branchRules(): array
    {
        return [
            static function (array $row): ?string {
                if (empty(trim((string) ($row['name'] ?? '')))) {
                    return 'branch name is required';
                }
                return null;
            },
        ];
    }
}

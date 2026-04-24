<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\LegacySqlImportService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('legacy:import-sql {file} {--company-email=info@africanqueenadventures.com} {--dry-run}', function (LegacySqlImportService $service): void {
    $file = (string) $this->argument('file');
    $companyEmail = (string) $this->option('company-email');
    $dryRun = (bool) $this->option('dry-run');

    if ($dryRun) {
        $this->warn('Running in dry-run mode. Database changes will be rolled back.');
    }

    try {
        $summary = $service->importFile($file, $companyEmail, $dryRun);

        foreach ($summary as $key => $value) {
            $this->line(sprintf('%s: %s', $key, (string) $value));
        }

        $this->info('Legacy SQL import completed.');
    } catch (\RuntimeException $e) {
        if ($e->getMessage() === '__DRY_RUN_ROLLBACK__') {
            $this->info('Dry-run completed successfully.');
            return;
        }

        throw $e;
    }
})->purpose('Import legacy SQL export into current Itinex schema.');

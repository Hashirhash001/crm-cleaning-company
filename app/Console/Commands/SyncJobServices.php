<?php

namespace App\Console\Commands;

use App\Models\Job;
use App\Models\Lead;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncJobServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:sync-services {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync job services from related leads to job_service pivot table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        $this->info('Starting job services sync...');
        $this->newLine();

        // Get all jobs
        $jobs = Job::with(['lead.services', 'services'])->get();

        $synced = 0;
        $skipped = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar($jobs->count());
        $progressBar->start();

        foreach ($jobs as $job) {
            $progressBar->advance();

            try {
                // Skip if job already has services synced
                if ($job->services()->count() > 0) {
                    $skipped++;
                    continue;
                }

                // Get services from related lead
                $serviceIds = [];

                if ($job->lead_id && $job->lead) {
                    // Get services from the lead
                    $serviceIds = $job->lead->services()->pluck('services.id')->toArray();
                } elseif ($job->service_id) {
                    // Fallback to single service_id field
                    $serviceIds = [$job->service_id];
                }

                // Skip if no services found
                if (empty($serviceIds)) {
                    $skipped++;
                    continue;
                }

                // Sync services to job
                if (!$dryRun) {
                    $job->services()->sync($serviceIds);
                    Log::info("Synced services for Job {$job->job_code}", [
                        'job_id' => $job->id,
                        'service_ids' => $serviceIds
                    ]);
                }

                $synced++;

            } catch (\Exception $e) {
                $errors++;
                Log::error("Error syncing services for Job {$job->job_code}: " . $e->getMessage());
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display summary
        $this->info('✅ Sync completed!');
        $this->newLine();

        $this->table(
            ['Status', 'Count'],
            [
                ['✅ Synced', $synced],
                ['⏭️  Skipped (already synced)', $skipped],
                ['❌ Errors', $errors],
                ['📊 Total', $jobs->count()],
            ]
        );

        if ($dryRun) {
            $this->newLine();
            $this->warn('⚠️  This was a DRY RUN. Run without --dry-run to apply changes.');
        }

        return Command::SUCCESS;
    }
}

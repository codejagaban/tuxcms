<?php

namespace App\Console\Commands;

use App\Models\Enquiry;
use App\Models\JobApplication;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Held spam is kept for a while so a wrong call can be put right, then
 * removed so it does not pile up. Anything released from the dashboard is
 * no longer marked as spam and is never touched.
 */
class PruneSpam extends Command
{
    public const KEEP_DAYS = 30;

    protected $signature = 'spam:prune {--days= : Delete held submissions older than this many days}';

    protected $description = 'Delete submissions the spam filter has held for too long';

    public function handle(): int
    {
        $before = now()->subDays((int) ($this->option('days') ?? self::KEEP_DAYS));

        $enquiries = Enquiry::where('delivery_status', 'spam')->where('created_at', '<', $before)->delete();

        $applications = 0;
        JobApplication::where('delivery_status', 'spam')->where('created_at', '<', $before)
            ->each(function (JobApplication $application) use (&$applications) {
                // The uploaded CV goes with it.
                if ($application->cv_path) {
                    Storage::disk('local')->delete($application->cv_path);
                }

                $application->delete();
                $applications++;
            });

        $this->info("Deleted {$enquiries} held enquiries and {$applications} held applications.");

        return self::SUCCESS;
    }
}

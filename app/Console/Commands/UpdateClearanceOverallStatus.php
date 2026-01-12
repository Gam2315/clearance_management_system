<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clearance;

class UpdateClearanceOverallStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clearance:update-overall-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update overall status for all clearance records based on individual department statuses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating overall status for all clearance records...');

        $clearances = Clearance::all();
        $updated = 0;

        foreach ($clearances as $clearance) {
            $oldStatus = $clearance->overall_status;
            $newStatus = $clearance->updateOverallStatus();

            if ($oldStatus !== $newStatus) {
                $updated++;
                $this->line("Clearance ID {$clearance->id}: {$oldStatus} → {$newStatus}");
            }
        }

        $this->info("Updated {$updated} out of {$clearances->count()} clearance records.");

        return 0;
    }
}

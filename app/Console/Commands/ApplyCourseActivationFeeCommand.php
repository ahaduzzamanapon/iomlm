<?php

namespace App\Console\Commands;

use App\Services\AccountingService;
use Illuminate\Console\Command;

class ApplyCourseActivationFeeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fees:apply-activation-fee {--force : Force apply even if today is on or before 10th}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Apply ৳100 monthly Course Activation Fee for active students with unpaid dues past the 10th of the month';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = $this->option('force');
        $this->info("Running Course Activation Fee processor (force=" . ($force ? 'true' : 'false') . ")...");

        $result = AccountingService::applyCourseActivationFees(now(), (bool) $force);

        if ($result['status'] === 'success') {
            $this->info("✓ " . $result['message']);
        } else {
            $this->warn("! " . $result['message']);
        }

        return Command::SUCCESS;
    }
}

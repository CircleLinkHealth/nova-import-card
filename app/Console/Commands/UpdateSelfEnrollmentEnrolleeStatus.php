<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateSelfEnrollmentEnrolleeStatus extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status on auto enrolment triggered Enrollees to call_queue.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:self-enrolment-triggered-enrollees {practiceId}';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $practiceId = $this->argument('practiceId');

        if ( ! $practiceId) {
            $this->warn('Please enter practice id for enrollees to update.');

            return;
        }

        $updated = Enrollee:: where('practice_id', $practiceId)
            ->whereIn('status', [Enrollee::QUEUE_AUTO_ENROLLMENT])
            ->where('enrollment_non_responsive', true)
            ->where('auto_enrollment_triggered', true)
            ->update([
                'status' => Enrollee::TO_CALL,
            ]);

        Log::info("Update Enrollees of practice $practiceId command status: $updated");
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Console\Commands\CommandsToUpdateOnProduction;

use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateSelfEnrollmentEnrolleeStatus extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status of Self Enrolment Enrollees.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:self-enrolment-enrollees {practiceId} {fromStatus} {toStatus} {enrolleeIds?*}';

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
        $practiceId = intval($this->argument('practiceId'));
        $fromStatus = $this->argument('fromStatus');
        $toStatus = $this->argument('toStatus');
        $enrolleeIds = $this->argument('enrolleeIds');

        $updated = Enrollee::with('user.patientInfo')
            ->where('practice_id', $practiceId)
            ->whereHas('user.patientInfo', function ($patient){
                $patient->where('ccm_status', Patient::UNREACHABLE);
            })
            ->when($enrolleeIds, function ($enrollees) use ($enrolleeIds){
                $enrollees->whereIn('id', $enrolleeIds);
            })
            ->where('status', $fromStatus)
            ->whereNull('source')
            ->update([
                'status' => $toStatus,
            ]);

        $this->info("Update Enrollees of practice $practiceId command status: $updated");
    }
}

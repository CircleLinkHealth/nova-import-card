<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Services\Calls\SchedulerService;
use App\User;
use CircleLinkHealth\Customer\Entities\Patient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckEnrolledPatientsForScheduledCalls extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make sure enrolled patients have at least one scheduled call.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calls:check {userIds? : comma separated. leave empty to check for all}';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Make sure all enrolled patients have scheduled calls.
     */
    public function handle(SchedulerService $schedulerService)
    {
        $userIds = $this->argument('userIds') ?? null;
        if (null != $userIds) {
            $userIds = explode(',', $userIds);
        }

        $loop  = 0;
        $fixed = 0;
        User::ofType('participant')
            ->when(
                ! empty($userIds),
                function ($q) use ($userIds) {
                    $q->whereIn('id', $userIds);
                }
            )
            ->whereHas(
                'patientInfo',
                function ($q) {
                    $q->enrolled();
                }
            )
            ->with(['inboundScheduledCalls'])
            ->each(
                function (User $patient) use ($schedulerService, &$fixed, &$loop) {
                    ++$loop;
                    if ($patient->inboundScheduledCalls->isNotEmpty()) {
                        return;
                    }

                    ++$fixed;

                    if ($this->shouldScheduleCall($patient)) {
                        $schedulerService->ensurePatientHasScheduledCall($patient, 'calls:check');
                    }
                }
            );

        $this->info("Went through $loop patients. Scheduled $fixed call(s). Done.");
    }

    private function shouldScheduleCall(User $patient): bool
    {
        $patient->loadMissing(['carePlan', 'patientInfo']);

        if (Patient::ENROLLED != $patient->patientInfo->ccm_status) {
            return false;
        }

        if ( ! $patient->carePlan) {
            Log::error("Patient[$patient->id] does not have care plan but is enrolled!");

            return false;
        }

        if ($patient->carePlan->isClhAdminApproved()) {
            return true;
        }

        if ($patient->carePlan->isProviderApproved()) {
            return true;
        }

        return false;
    }
}

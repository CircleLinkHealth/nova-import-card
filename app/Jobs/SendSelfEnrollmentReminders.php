<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Helpers\SelfEnrollmentHelpers;
use App\Services\Enrollment\EnrollmentInvitationService;
use App\Traits\EnrollableManagement;
use App\Traits\EnrollmentReminderShared;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class SendSelfEnrollmentReminders implements ShouldQueue
{
    use Dispatchable;
    use EnrollableManagement;
    use EnrollmentReminderShared;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    const REMIND_ENROLLEES                           = 'enrollees';
    const REMIND_UNREACHABLE_PATIENTS                = 'unreachable_patients';
    const TAKE_FINAL_ACTION_ON_UNRESPONSIVE_PATIENTS = 'final_action_on_unreachable_patients_and_enrollees';

    /**
     * @var string
     */
    private $groupToRemind;

    /**
     * SendSelfEnrollmentReminders constructor.
     */
    public function __construct(string $groupToRemind)
    {
        $this->groupToRemind = $groupToRemind;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ( ! in_array($this->groupToRemind, [self::REMIND_ENROLLEES, self::REMIND_UNREACHABLE_PATIENTS, self::TAKE_FINAL_ACTION_ON_UNRESPONSIVE_PATIENTS])) {
            throw new \Exception("Unknown group `{$this->groupToRemind}`. Valid options are `".self::REMIND_UNREACHABLE_PATIENTS.'` and '.self::REMIND_ENROLLEES);
        }

        $testingMode = filter_var(AppConfig::pull('testing_enroll_sms', true), FILTER_VALIDATE_BOOLEAN) || App::environment('testing');

        if ($testingMode) {
            $practiceId    = SelfEnrollmentHelpers::getDemoPractice()->id;
            $twoDaysAgo    = now()->startOfDay();
            $untilEndOfDay = $twoDaysAgo->copy()->endOfDay();
        } else {
            $practiceId    = null;
            $twoDaysAgo    = now()->copy()->subHours(48)->startOfDay();
            $untilEndOfDay = $twoDaysAgo->copy()->endOfDay();
        }

        if (self::TAKE_FINAL_ACTION_ON_UNRESPONSIVE_PATIENTS === $this->groupToRemind) {
            $this->handleUnresponsives();

            return;
        }

        $query = null;

        if (self::REMIND_UNREACHABLE_PATIENTS === $this->groupToRemind) {
            $query = $this->getUnreachablePatientsToSendReminder($untilEndOfDay, $twoDaysAgo, $practiceId);
        } elseif (self::REMIND_ENROLLEES === $this->groupToRemind) {
            $query = $this->getEnrolleeUsersToSendReminder($untilEndOfDay, $twoDaysAgo, $practiceId);
        }

        if (is_null($query)) {
            return;
        }

        $query->chunk(100, function ($users) {
            $users->each(function (User $enrollable) {
                SendSelfEnrollmentReminder::dispatch($enrollable);
            });
        });
    }

    private function getEnrolleeUsersToSendReminder(Carbon $untilEndOfDay, Carbon $twoDaysAgo, ?int $practiceId = null)
    {
        return $this->sharedReminderQuery($untilEndOfDay, $twoDaysAgo)
            ->whereHas('enrollee', function ($enrollee) {
                $enrollee->whereNull('source'); //Eliminates unreachable patients, and only fetches enrollees who have not yet enrolled.
            })->orderBy('created_at', 'asc')
            ->when($practiceId, function ($q) use ($practiceId) {
                return $q->where('program_id', $practiceId);
            });
    }

    private function getUnreachablePatientsToSendReminder(Carbon $untilEndOfDay, Carbon $twoDaysAgo, ?int $practiceId = null)
    {
        return $this->sharedReminderQuery($untilEndOfDay, $twoDaysAgo)
            ->whereHas('enrollee', function ($enrollee) {
                $enrollee->where('source', '=', Enrollee::UNREACHABLE_PATIENT); //  It's NOT Original enrollee.
            })
            ->when($practiceId, function ($q) use ($practiceId) {
                return $q->where('program_id', $practiceId);
            });
    }

    private function handleUnresponsives(Carbon $untilEndOfDay, Carbon $twoDaysAgo, ?int $practiceId = null)
    {
        $this->unresponsivesQuery($untilEndOfDay, $twoDaysAgo, $practiceId)->chunk(100, function ($users) {
            $users->each(function (User $noResponsivePatient) {
                $enrollmentInvitationService = app(EnrollmentInvitationService::class);

                if ( ! $noResponsivePatient->isSurveyOnly()) {
//                    We need the enrolle model created when patient became "unreachable"......
//                    (see.PatientObserver & UnreachablePatientsToCaPanel)
//                   ...to set call_queue - doing this as temp. solution in order to be displayed on CA PANEL
                    $enrollmentInvitationService->putIntoCallQueue($noResponsivePatient->enrollee);

                    return;
                }

                if ((bool) optional($noResponsivePatient->enrollee->selfEnrollmentStatus)->logged_in) {
                    $enrollmentInvitationService->putIntoCallQueue($noResponsivePatient->enrollee);

                    return;
                }

//                        Mark as non responsive means they will get a physical MAIL.
                $enrollmentInvitationService->markAsNonResponsive($noResponsivePatient->enrollee);
                $enrollmentInvitationService->putIntoCallQueue($noResponsivePatient->enrollee);
            });
        });
    }

    private function unresponsivesQuery(Carbon $untilEndOfDay, Carbon $twoDaysAgo, ?int $practiceId = null)
    {
        return User::whereHas('notifications', function ($notification) use ($untilEndOfDay, $twoDaysAgo) {
            $notification->where([
                ['created_at', '<=', $untilEndOfDay],
                ['created_at', '>=', $twoDaysAgo],
            ])->selfEnrollmentInvites();
        })
            ->whereHas('patientInfo', function ($patient) {
                $patient->where('ccm_status', Patient::UNREACHABLE);
            })->when($practiceId, function ($q) use ($practiceId) {
                return $q->where('program_id', $practiceId);
            })->has('enrollee')->with('enrollee');
    }
}

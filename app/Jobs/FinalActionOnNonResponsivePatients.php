<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Notifications\SendEnrollmentEmail;
use App\Services\Enrollment\EnrollmentInvitationService;
use App\Traits\EnrollableManagement;
use App\Traits\UnreachablePatientsToCaPanel;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class FinalActionOnNonResponsivePatients implements ShouldQueue
{
//    This is the final (out of 3) action taken on NON-RESPONSIVE enrolees OR patients.
//    If User has role "survey-only" then enrollment letter will be send with enrollment call on delivery.
//    If User is not "survey-only" then patients will be put into queue for enrollment calls.
//    Even if patient has survey in progress, will get a call
//    ONLY NON "survey-only" (meaning. enrollees) will get a physical mail.

    use Dispatchable;
    use EnrollableManagement;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var EnrollmentInvitationService
     */
    private $enrollmentInvitationService;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->enrollmentInvitationService = app(EnrollmentInvitationService::class);
    }

    /**
     * @param $practiceId
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function getPracticeEnrollmentLetter($practiceId)
    {
        return EnrollmentInvitationLetter::where('practice_id', $practiceId)->firstOrFail();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
//        @TODO:refactor this class when in call with michalis.
        //        Two days after the last reminder - (the "SendEnrollmentNotificationsReminder")
        $twoDaysAgo    = Carbon::parse(now())->copy()->subHours(48)->startOfDay()->toDateTimeString();
        $untilEndOfDay = Carbon::parse($twoDaysAgo)->endOfDay()->toDateTimeString();
//        @todo:Set to false in config
        $testingMode = filter_var(AppConfig::pull('testing_enroll_sms', true), FILTER_VALIDATE_BOOLEAN);
        if ($testingMode) {
            $twoDaysAgo    = Carbon::parse(now())->startOfDay()->toDateTimeString();
            $untilEndOfDay = Carbon::parse($twoDaysAgo)->copy()->endOfDay()->toDateTimeString();
            $practice      = $this->getDemoPractice();
            $users         = $this->usersForFinalAction($twoDaysAgo, $untilEndOfDay)
                ->where('program_id', $practice->id)
                ->get();
            $this->takeAction($users);
        } else {
            $users = $this->usersForFinalAction($twoDaysAgo, $untilEndOfDay)->get();
            $this->takeAction($users);
        }
    }

    /**
     * @param $noResponsivePatient
     * @param $pages
     */
    public function sendLetterWithRegularMail($noResponsivePatient, $pages)
    {
//        Will be the same page where the admin can send the notifs
//        $noResponsivePatient->notify(new SendEnrollmentLetterToNonResponsivePatients($noResponsivePatient, $pages));
    }

    /**
     * @param $users
     *
     * @return mixed
     */
    private function takeAction($users)
    {
        return $users->each(function (User $noResponsivePatient) {
            $isSurveyOnlyUser = $noResponsivePatient->hasRole('survey-only');
            /** @var Enrollee $enrollee */
            $enrollee = $this->getEnrollee($noResponsivePatient->id);
            if ( ! $enrollee) {
                Log::warning("Enrollee model not found for user $noResponsivePatient->id");

                return;
            }
            if ($isSurveyOnlyUser) {
                if ($this->enrolleeHasLoggedIn($enrollee)) {
//                    Don't send regular mail
                    $this->enrollmentInvitationService->putIntoCallQueue($enrollee);
                } else {
//                        Mark as non responsive means they will get a physical MAIL.
                    $this->enrollmentInvitationService->markAsNonResponsive($enrollee);
                    $this->enrollmentInvitationService->putIntoCallQueue($enrollee);
                }
//                    Keeping this maybe we need the letter to be printed from nova
//                    $practice = $noResponsivePatient->primaryPractice;
//                    $provider = $this->getEnrollableProvider($isSurveyOnlyUser, $noResponsivePatient);
//                    $careAmbassadorPhoneNumber = $practice->outgoing_phone_number;
//                    $practiceName = $practice->name;
//                    $letter = $this->getPracticeEnrollmentLetter($practice->id);
//                    $pages = $this->enrollmentInvitationService->createLetter($practiceName, $letter, $careAmbassadorPhoneNumber, $provider, false);
//                    $this->sendLetterWithRegularMail($noResponsivePatient, $pages);
            } else {
//                    We need the enrolle model created when patient became "unreachable"......
//                    (see.PatientObserver & UnreachablePatientsToCaPanel)
//                   ...to set call_queue - doing this as temp. solution in order to be displayed on CA PANEL
                $this->enrollmentInvitationService->putIntoCallQueue($enrollee);
            }
        });
    }

    private function usersForFinalAction(string $twoDaysAgo, string $untilEndOfDay)
    {
//       Note to consider - Here Im depending only on notifications as source of truth.
        return User::whereHas('notifications', function ($notification) use ($untilEndOfDay, $twoDaysAgo) {
            $notification->where([
                ['created_at', '<=', $untilEndOfDay],
                ['created_at', '>=', $twoDaysAgo],
            ])->where('type', SendEnrollmentEmail::class)
                ->where('data->is_reminder', true);
        })
            ->whereHas('patientInfo', function ($patient) {
                $patient->where('ccm_status', Patient::UNREACHABLE);
            });
    }
}

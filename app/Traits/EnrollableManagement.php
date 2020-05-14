<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

use App\Http\Controllers\Enrollment\AutoEnrollmentCenterController;
use App\Notifications\SendEnrollmentEmail;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Entities\SelfEnrollmentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

trait EnrollableManagement
{
    //@todo: Move methods used in one place to that place.

    /**
     * @param $enrollable
     *
     * @return bool
     */
    public function activeEnrollmentInvitationsExists($enrollable)
    {
        return $enrollable->enrollmentInvitationLink->where('manually_expired', false)->exists();
    }

    /**
     * @param $notifiable
     * @param $data
     *
     * @return string
     */
    public function createInvitationLink($notifiable)
    {
        $url = URL::temporarySignedRoute('invitation.enrollment.loginForm', now()->addHours(48), $this->notificationContent['urlData']);

        $shortUrl = null;
        try {
            $shortUrl = shortenUrl($url);
        } catch (\Exception $e) {
            \Log::warning($e->getMessage());
        }

        $urlToken = $this->parseUrl($url);
        $this->saveTemporaryInvitationLink($notifiable, $urlToken, $url);

        return $shortUrl ?? $url;
    }

    /**
     * @param $enrollableId
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function createUrlAndRedirectToSurvey($enrollableId)
    {
        $enrolleesSurveyInstance = $this->getEnrolleesSurveyInstance();

        try {
            $surveyId = $enrolleesSurveyInstance->survey_id;
        } catch (\Exception $exception) {
            \Log::critical('Survey instance not found');
            abort(404);
        }

        $this->updateAwvUsersSurvey($enrollableId, $enrolleesSurveyInstance, $surveyId);
        $enrolleesSurveyUrl = url(config('services.awv.url')."/survey/enrollees/create-url/{$enrollableId}/{$surveyId}");

        return redirect($enrolleesSurveyUrl);
    }

    /**
     * @param $enrollable
     */
    public function expirePastInvitationLink($enrollable)
    {
        Log::debug("expirePastInvitationLink called for $enrollable->id");
        $pastInvitationLinks = $this->pastActiveInvitationLink($enrollable);
        if ( ! empty($pastInvitationLinks)) {
            $pastInvitationLinks->update(['manually_expired' => true]);
        }
    }

    /**
     * @param $surveyInstance
     * @param mixed $userId
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function getAwvUserSurvey($userId, $surveyInstance)
    {
        return DB::table('users_surveys')
            ->where('user_id', '=', $userId)
            ->where('survey_instance_id', '=', $surveyInstance->id);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function getDemoPractice()
    {
        return Practice::where('name', '=', 'demo')->firstOrFail();
    }

    /**
     * @return \App\User|Enrollee|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getEnrollableModelType(User $user)
    {
        return $user->isSurveyOnly()
            ? $this->getEnrollee($user->id)
            : $this->getUserModelEnrollee($user->id);
    }

    /**
     * @param $isSurveyOnlyUser
     *
     * @return \CircleLinkHealth\Customer\Entities\ProviderInfo|mixed|User|null
     */
    public function getEnrollableProvider($isSurveyOnlyUser, User $enrollable)
    {
        return $isSurveyOnlyUser
            ? $this->getSurveyOnlyUserProvider($enrollable->id)
            : $enrollable->providerInfo;
    }

    /**
     * @return Enrollee|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getEnrollee(string $enrollableId)
    {
        return Enrollee::whereUserId($enrollableId)->first();
    }

    public function getEnrolleeFromNotification($enrollableId)
    {
        $notification = DatabaseNotification::where('type', SendEnrollmentEmail::class)
            ->where('notifiable_id', $enrollableId)
            ->first();

        return Enrollee::whereId($notification->data['enrollee_id'])->first();
    }

    /**
     * NOTE: "whereDoesntHave" makes sure we dont invite Unreachable/Non responded - Enrollees second time.
     *
     * @param $practiceId
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getEnrollees($practiceId)
    {
//        CHECK FOR SURVEY ONLY TOGETHER WITH USER_ID
        return Enrollee::where('user_id', null)
            ->where('practice_id', $practiceId)
            ->whereDoesntHave('enrollmentInvitationLink')
            ->whereIn('status', [
                Enrollee::QUEUE_AUTO_ENROLLMENT,
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public function getEnrolleesSurveyInstance()
    {
        return DB::table('surveys')
            ->join('survey_instances', 'surveys.id', '=', 'survey_instances.survey_id')
            ->where('name', '=', AutoEnrollmentCenterController::ENROLLEES)->first();
    }

    public function getEnrolleeSurvey()
    {
        return DB::table('surveys')
            ->where('name', '=', AutoEnrollmentCenterController::ENROLLEES)
            ->first();
    }

    /**
     * @param $patientInfoId
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public function getSurveyInvitationLink($patientInfoId)
    {
        return DB::table('invitation_links')
            ->where('patient_info_id', $patientInfoId)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * @param $enrollableId
     *
     * @return \CircleLinkHealth\Customer\Entities\User|mixed|null
     */
    public function getSurveyOnlyUserProvider($enrollableId)
    {
        $enrollee = Enrollee::with('provider')
            ->whereUserId($enrollableId)
            ->firstOrFail();

        return $enrollee->provider;
    }

    /**
     * @return \App\User|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getUserModelEnrollee(string $enrollableId)
    {
        return User::whereId($enrollableId)->first();
    }

    /**
     * @param $notifiable
     *
     * @return bool
     */
    public function hasSurveyCompleted($notifiable)
    {
        //        For nova request. At that point enrollees will ot have User model, hence they didnt get invited yet.
//        if (Enrollee::class === get_class($notifiable)) {
//            return false;
//        }
        $surveyLink = $this->getSurveyInvitationLink($notifiable->patientInfo->id);
        if ( ! empty($surveyLink)) {
            $surveyInstance = DB::table('survey_instances')
                ->where('survey_id', '=', $surveyLink->survey_id)
                ->first();

            return $this->getAwvUserSurvey($notifiable->id, $surveyInstance)
                ->where('status', '=', 'completed')
                ->exists();
        }

        return false;
    }

    /**
     * @param $notifiable
     *
     * @return bool
     */
    public function hasSurveyInProgress($notifiable)
    {
//        For nova request. At that point enrollees will ot have User model, hence they didnt get invited yet.
//        if (Enrollee::class === get_class($notifiable)) {
//            return false;
//        }
        $surveyLink = $this->getSurveyInvitationLink($notifiable->patientInfo->id);
        if ( ! empty($surveyLink)) {
            $surveyInstance = DB::table('survey_instances')
                ->where('survey_id', '=', $surveyLink->survey_id)
                ->first();

            return DB::table('users_surveys')
                ->where('user_id', '=', $notifiable->id)
                ->where('survey_instance_id', '=', $surveyInstance->id)
                ->where('status', '=', 'in_progress')
                ->exists();
        }

        return false;
    }

    /**
     *  Requirement: Did patient view Letter or Survey?
     *  If logged in once then user did view the letter. If this exists the no need need to check further.
     *
     * @param mixed $enrollableId
     * @param mixed $enrollee
     *
     * @return bool
     */
    public function hasViewedLetterOrSurvey($enrollee)
    {
        return optional($enrollee->selfEnrollmentStatuses)->logged_in;
    }

    /**
     * @param $url
     *
     * @return mixed
     */
    public function parseUrl($url)
    {
        $parsedUrl = parse_url($url);
        parse_str($parsedUrl['query'], $output);

        return $output['signature'];
    }

    /**
     * @param $enrollable
     *
     * @return mixed
     */
    public function pastActiveInvitationLink($enrollable)
    {
        return $enrollable->enrollmentInvitationLink()->where('manually_expired', false)->first();
    }

    /**
     * @param $urlToken
     * @param $url
     */
    public function saveTemporaryInvitationLink(User $notifiable, $urlToken, $url)
    {
        if ($notifiable->isSurveyOnly()) {
            $notifiable = Enrollee::whereUserId($notifiable->id)->firstOrFail();
        }
        //  Expire previous INVITATION link if exists
        $this->expirePastInvitationLink($notifiable);

        $notifiable->enrollmentInvitationLink()->create([
            'link_token'       => $urlToken,
            'url'              => $url,
            'manually_expired' => false,
            'button_color'     => $this->color,
        ]);
    }

    /**
     * @param $enrollableId
     * @param $enrolleesSurveyInstance
     * @param $surveyId
     */
    public function updateAwvUsersSurvey($enrollableId, $enrolleesSurveyInstance, $surveyId)
    {
        //        Enroll user to awv - table: "users_survey"
        $usersSurveys = DB::table('users_surveys');
//        Update status
        $usersSurveys->updateOrInsert(
            [
                'user_id'            => $enrollableId,
                'survey_instance_id' => $enrolleesSurveyInstance->id,
                'survey_id'          => $surveyId,
            ],
            [
                'status'     => 'pending',
                'start_date' => Carbon::parse(now())->toDateTimeString(),
            ]
        );
    }

    public function updateEnrolleeSurveyStatuses(
        $enrolleeId,
        $userId = null,
        $statusSurvey = null,
        $loggedIn = false,
        $patientInfo = null
    ) {
        SelfEnrollmentStatus::updateOrCreate(
            [
                'enrollee_id' => $enrolleeId,
            ],
            [
                'enrollee_user_id'      => $userId,
                'awv_survey_status'     => $statusSurvey,
                'enrollee_patient_info' => $patientInfo,
            ]
        );
    }
}

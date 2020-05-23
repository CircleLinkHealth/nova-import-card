<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

use App\Http\Controllers\Enrollment\AutoEnrollmentCenterController;
use App\Notifications\SendEnrollmentEmail;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Entities\SelfEnrollmentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

trait EnrollableManagement
{
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
     * @return \App\User|Enrollee|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getEnrollableModelType(User $user)
    {
        return $user->isSurveyOnly()
            ? Enrollee::fromUserId($user->id)
            : User::find($user->id);
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

    public function getEnrolleeFromNotification($enrollableId)
    {
        $notification = DatabaseNotification::where('type', SendEnrollmentEmail::class)
            ->where('notifiable_id', $enrollableId)
            ->first();

        return Enrollee::whereId($notification->data['enrollee_id'])->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public function getEnrolleesSurveyInstance()
    {
        return DB::table('surveys')
            ->join('survey_instances', 'surveys.id', '=', 'survey_instances.survey_id')
            ->where('name', '=', AutoEnrollmentCenterController::ENROLLEES_SURVEY_NAME)->first();
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
        return optional($enrollee->selfEnrollmentStatus)->logged_in;
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
        return $enrollable->enrollmentInvitationLinks()->where('manually_expired', false)->first();
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

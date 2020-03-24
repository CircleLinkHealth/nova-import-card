<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

trait EnrollableManagement
{
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
    public function createInvitationLink($notifiable, $data)
    {
        $url = URL::temporarySignedRoute('invitation.enrollment', now()->addHours(48), $data);
//        try {
//            $shortUrl = UrlShortener::shorten($url);
//        } catch (\Exception $e) {
//            \Log::warning($e->getMessage());
//        }
        $urlToken = $this->parseUrl($url);
        $this->saveTemporaryInvitationLink($notifiable, $urlToken, $url);

        return $url;
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

    public function saveTemporaryInvitationLink(User $notifiable, $urlToken, $url)
    {
        $receiver = $notifiable;
        if ($receiver->checkForSurveyOnlyRole()) {
            $receiver = Enrollee::whereUserId($receiver->id)->firstOrFail();
        }
//        Expire previous INVITATION link if exists
        $this->expirePastInvitationLink($notifiable);
        $receiver->enrollmentInvitationLink()->create([
            'link_token' => $urlToken,
            'url' => $url,
            'manually_expired' => false,
        ]);
    }

    /**
     * @param $enrollable
     */
    public function expirePastInvitationLink($enrollable)
    {
        $pastInvitationLinks = $this->pastActiveInvitationLinks($enrollable);
        if (!empty($pastInvitationLinks)) {
            $pastInvitationLinks->update(['manually_expired' => true]);
        }
    }

    /**
     * @param $enrollable
     *
     * @return mixed
     */
    public function pastActiveInvitationLinks($enrollable)
    {
        return $enrollable->enrollmentInvitationLink()->where('manually_expired', false)->first();
    }

    /**
     * @param $enrollableId
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function createUrlAndRedirectToSurvey($enrollableId)
    {
        $enrolleesSurveyInstance = $this->getEnrolleesSurveyInstance();
        $surveyId = $enrolleesSurveyInstance->survey_id;
        $this->updateAwvUsersSurvey($enrollableId, $enrolleesSurveyInstance, $surveyId);
        $enrolleesSurveyUrl = url(config('services.enrollees-survey.url') . "/survey/enrollees/create-url/{$enrollableId}/{$surveyId}");

        return redirect($enrolleesSurveyUrl);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public function getEnrolleesSurveyInstance()
    {
        return DB::table('surveys')
            ->join('survey_instances', 'surveys.id', '=', 'survey_instances.survey_id')
            ->where('name', '=', 'Enrollees')->first();
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
                'user_id' => $enrollableId,
                'survey_instance_id' => $enrolleesSurveyInstance->id,
                'survey_id' => $surveyId,
            ],
            [
                'status' => 'pending',
                'start_date' => Carbon::parse(now())->toDateTimeString(),
            ]
        );
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
    public function hasSurveyCompleted($notifiable)
    {
        $surveyLink = $this->getSurveyInvitationLink($notifiable->patientInfo->id);
        if (!empty($surveyLink)) {
            $surveyInstance = DB::table('survey_instances')
                ->where('survey_id', '=', $surveyLink->survey_id)
                ->first();

            return $this->getAwvUserSurvey($notifiable, $surveyInstance)
                ->where('status', '=', 'completed')
                ->exists();
        }

        return false;
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
     * @param $surveyInstance
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function getAwvUserSurvey(User $notifiable, $surveyInstance)
    {
        return DB::table('users_surveys')
            ->where('user_id', '=', $notifiable->id)
            ->where('survey_instance_id', '=', $surveyInstance->id);
    }

    /**
     * @param $notifiable
     *
     * @return bool
     */
    public function hasSurveyInProgress($notifiable)
    {
        $surveyLink = $this->getSurveyInvitationLink($notifiable->patientInfo->id);
        if (!empty($surveyLink)) {
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
}

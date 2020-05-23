<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Helpers;

use App\Http\Controllers\Enrollment\SelfEnrollmentController;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class SelfEnrollmentHelpers
{
    /**
     * @param $surveyInstance
     * @param mixed $userId
     */
    public static function awvUserSurveyQuery(User $patient, object $surveyInstance): Builder
    {
        return DB::table('users_surveys')
            ->where('user_id', '=', $patient->id)
            ->where('survey_instance_id', '=', $surveyInstance->id);
    }

    public static function getCurrentYearEnrolleeSurveyInstance(): object
    {
        return \Cache::remember('current_year_self_enrollment_survey_instance_'.now()->year.'_'.SelfEnrollmentController::ENROLLEES_SURVEY_NAME, 2, function () {
            return DB::table('survey_instances')
                ->where('survey_id', '=', self::getEnrolleeSurvey()->id)
                ->where('year', '=', now()->year)
                ->firstOrFail();
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public static function getDemoPractice()
    {
        return \Cache::remember('demo_practice_object', 2, function () {
            return Practice::where('name', '=', 'demo')->firstOrFail();
        });
    }

    /**
     * @return \App\User|Enrollee|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public static function getEnrollableModel(User $user)
    {
        return $user->isSurveyOnly()
            ? Enrollee::fromUserId($user->id)
            : User::find($user->id);
    }

    public static function getEnrolleeSurvey(): object
    {
        return \Cache::remember('self_enrollment_survey_'.SelfEnrollmentController::ENROLLEES_SURVEY_NAME, 2, function () {
            return DB::table('surveys')
                ->where('name', '=', SelfEnrollmentController::ENROLLEES_SURVEY_NAME)
                ->firstOrFail();
        });
    }

    /**
     * @param $patientInfoId
     *
     * @return Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public static function getSurveyInvitationLink(Patient $patient): ?object
    {
        return DB::table('invitation_links')
            ->where('patient_info_id', $patient->id)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * @param $url
     *
     * @return mixed
     */
    public static function getTokenFromUrl(string $url): ?string
    {
        $parsedUrl = parse_url($url);

        if ( ! is_array($parsedUrl)) {
            return null;
        }
        if ( ! array_key_exists('query', $parsedUrl)) {
            return null;
        }
        parse_str($parsedUrl['query'], $output);

        return $output['signature'];
    }

    public static function hasCompletedSelfEnrollmentSurvey(User $user): bool
    {
        $user->loadMissing('patientInfo');

        $surveyLink = self::getSurveyInvitationLink($user->patientInfo);

        if (empty($surveyLink)) {
            return false;
        }

        $surveyInstance = DB::table('survey_instances')
            ->where('survey_id', '=', $surveyLink->survey_id)
            ->first();

        if (empty($surveyInstance)) {
            return false;
        }

        return self::awvUserSurveyQuery($user, $surveyInstance)
            ->where('status', '=', 'completed')
            ->exists();
    }
}

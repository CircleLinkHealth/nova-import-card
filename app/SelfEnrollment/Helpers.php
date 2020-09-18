<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment;

use App\Http\Controllers\Enrollment\SelfEnrollmentController;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class Helpers
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
            $surveyId = self::getEnrolleeSurvey()->id;

            $instance = DB::table('survey_instances')
                ->where('survey_id', '=', $surveyId)
                ->where('year', '=', now()->year)
                ->first();

            if ( ! $instance) {
                throw new \Exception("Could not find survey instance for survey with ID $surveyId");
            }

            return $instance;
        });
    }

    /**
     * @return \App\User|Enrollee|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public static function getEnrollableModel(User &$user)
    {
        $user->loadMissing('enrollee');

        return $user->isSurveyOnly()
            ? $user->enrollee
            : $user;
    }

    public static function getEnrolleeSurvey(): object
    {
        return \Cache::remember('self_enrollment_survey_'.SelfEnrollmentController::ENROLLEES_SURVEY_NAME, 2, function () {
            $survey = DB::table('surveys')
                ->where('name', '=', SelfEnrollmentController::ENROLLEES_SURVEY_NAME)
                ->first();

            if ( ! $survey) {
                throw new \Exception('Could not find survey with name '.SelfEnrollmentController::ENROLLEES_SURVEY_NAME);
            }

            return $survey;
        });
    }

    /**
     * @param $patientInfoId
     *
     * @return Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public static function getSurveyInvitationLink(User $user): ?object
    {
        return DB::table('enrollables_invitation_links')
            ->where('invitationable_id', $user->enrollee->id)
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
        $surveyLink = self::getSurveyInvitationLink($user);

        if (empty($surveyLink)) {
            return false;
        }

        $surveyInstance = self::getCurrentYearEnrolleeSurveyInstance();

        if (empty($surveyInstance)) {
            return false;
        }

        return self::awvUserSurveyQuery($user, $surveyInstance)
            ->where('status', '=', 'completed')
            ->exists();
    }
}

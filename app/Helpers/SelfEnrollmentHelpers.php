<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Helpers;

use App\Http\Controllers\Enrollment\SelfEnrollmentController;
use App\Notifications\SelfEnrollmentInviteNotification;
use Carbon\Carbon;
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
    
    public static function enrollableUsersToRemindQuery(Carbon $end, Carbon $start)
    {
        $from = $start->toDateTimeString();
        $to   = $end->toDateTimeString();

//         We send the first notification marked as is_reminder => false
//         We send the second notification(reminder => true).
//         We dont want to send a second reminder if user has 1 true and 1 false is_reminder.
        return User::whereHas('notifications', function ($notification) use ($to, $from) {
            $notification
                ->where('data->is_reminder', false)
                ->where([
                    ['created_at', '>=', $from],
                    ['created_at', '<=', $to],
                ])
                ->selfEnrollmentInvites();
        })
            ->whereHas('patientInfo', function ($patient) use ($from, $to) {
                $patient->where('ccm_status', Patient::UNREACHABLE);
            })
            ->whereDoesntHave('notifications', function ($notification) use ($to, $from) {
                $notification
                    ->where('data->is_reminder', true)
                    ->where([
                        ['created_at', '>=', $from],
                        ['created_at', '<=', $to],
                    ])
                    ->where('type', SelfEnrollmentInviteNotification::class);
            });
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Helpers;

use App\Http\Controllers\Enrollment\AutoEnrollmentCenterController;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
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

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public static function getDemoPractice()
    {
        return \Cache::remember('demo_practice_object', 2, function () {
            return Practice::where('name', '=', 'demo')->firstOrFail();
        });
    }

    public static function getEnrolleeSurvey(): object
    {
        return \Cache::remember('self_enrollment_survey_'.AutoEnrollmentCenterController::ENROLLEES_SURVEY_NAME, 2, function () {
            return DB::table('surveys')
                ->where('name', '=', AutoEnrollmentCenterController::ENROLLEES_SURVEY_NAME)
                ->first();
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

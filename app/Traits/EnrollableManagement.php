<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

use CircleLinkHealth\Eligibility\Entities\SelfEnrollmentStatus;

trait EnrollableManagement
{
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

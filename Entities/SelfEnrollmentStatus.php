<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;

class SelfEnrollmentStatus extends BaseModel
{
//    We could save all needed values for the enrolee invitation dashboard on this model.
//    But i think is better if they re in sync with the relationships that are used
//    in a lot of places in the app.
    protected $fillable = [
        'enrollee_id',
        'enrollee_user_id',
        'enrollee_patient_info',
        'awv_survey_status',
        'logged_in',
    ];
    protected $table = 'self_enrollment_statuses';
}

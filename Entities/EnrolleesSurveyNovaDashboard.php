<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;

class EnrolleesSurveyNovaDashboard extends BaseModel
{
    protected $fillable = [
        'enrollee_id',
        'user_id_from_enrollee',
        'awv_survey_status',
        'logged_in',
    ];
    protected $table = 'enrollees_nova_display';
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Core\Filters\Filterable;

/**
 * Class Survey.
 *
 * @property int patient_id
 * @property int practice_id
 * @property string patient_name
 * @property Carbon dob
 * @property string provider_name
 * @property string hra_status
 * @property string vitals_status
 * @property string year
 * @property Carbon appointment
 * @property string mrn
 */
class PatientAwvSurveyInstanceStatusView extends BaseModel
{
    use Filterable;

    protected $table = 'patient_awv_survey_instance_status_view';
}

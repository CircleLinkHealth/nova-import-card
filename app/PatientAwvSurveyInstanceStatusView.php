<?php

namespace App;

use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Core\Filters\Filterable;

/**
 * Class Survey
 *
 * @property-read int patient_id
 * @property-read string patient_name
 * @property-read Carbon dob
 * @property-read string provider_name
 * @property-read string hra_status
 * @property-read string vitals_status
 * @property-read string year
 *
 * @package App
 */
class PatientAwvSurveyInstanceStatusView extends BaseModel
{
    use Filterable;
}

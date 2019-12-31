<?php

namespace CircleLinkHealth\Customer\Entities;

use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\SqlViewModel;
use Illuminate\Database\Eloquent\Model;

/**
 * CircleLinkHealth\Customer\Entities\PatientAWVSurveyInstanceStatus
 *
 * @property int $patient_id
 * @property string $patient_name
 * @property string $practice_id
 * @property string|null $dob
 * @property string|null $provider_name
 * @property string|null $hra_status
 * @property string|null $vitals_status
 * @property int|null $year
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientAWVSurveyInstanceStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientAWVSurveyInstanceStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientAWVSurveyInstanceStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientAWVSurveyInstanceStatus whereDob($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientAWVSurveyInstanceStatus whereHraStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientAWVSurveyInstanceStatus wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientAWVSurveyInstanceStatus wherePatientName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientAWVSurveyInstanceStatus wherePracticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientAWVSurveyInstanceStatus whereProviderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientAWVSurveyInstanceStatus whereVitalsStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientAWVSurveyInstanceStatus whereYear($value)
 * @mixin \Eloquent
 * @property string|null $hra_display_date
 * @property string|null $v_display_date
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientAWVSurveyInstanceStatus whereHraDisplayDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientAWVSurveyInstanceStatus whereVDisplayDate($value)
 */
class PatientAWVSurveyInstanceStatus extends SqlViewModel
{
    protected $table = 'patient_awv_survey_instance_status_view';

    public $phi = [
        'patient_name',
        'provider_name'
    ];

    public function getHraDisplayDateAttribute($date){
        return Carbon::parse($date)->toDateString();
    }

    public function getVDisplayDateAttribute($date){
        return Carbon::parse($date)->toDateString();
    }
}

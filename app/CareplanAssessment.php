<?php namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

/**
 * App\CareplanAssessment
 *
 * @property int $id
 * @property int $careplan_id
 * @property string $alcohol_misuse_counseling
 * @property string $diabetes_screening_interval
 * @property string $diabetes_screening_last_and_next_date
 * @property string $diabetes_screening_risk
 * @property array|string|null $diabetes_screening_risk
 * @property string $eye_screening_last_and_next_date
 * @property string $key_treatment
 * @property array|string|null $patient_functional_assistance_areas
 * @property array|string|null $patient_psychosocial_areas_to_watch
 * @property string $risk
 * @property array|string|null $risk_factors
 * @property string $tobacco_misuse_counseling
 * @property int $provider_approver_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\CarePlan $carePlan
 * @mixin \Eloquent
 */
class CareplanAssessment extends \App\BaseModel
{

    public function load($object)
    {   
        foreach (get_object_vars($object) as $key => $value) {
            if ($key != '_token') $this->$key = $value;
        }
    }

    public function approver() {
        $this->belongsTo(User::class, 'provider_approver_id');
    }
    
    public function patient() {
        $this->belongsTo(CarePlan::class, 'careplan_id', 'user_id');
    }
}

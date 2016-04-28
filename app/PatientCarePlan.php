<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientCarePlan extends Model {

    public function careplantemplate()
    {
        return $this->belongsTo('App\CarePlanTemplate');
    }

    public function patient()
    {
        return $this->belongsTo(User::class,'patient_id');
    }

    public function getCarePlanTemplateIdAttribute($value)
    {
        return $value;
    }
    //To add functions to get user values

}

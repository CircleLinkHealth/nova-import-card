<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientCarePlan extends Model {

    protected $guarded = [];

    public function careplantemplate()
    {
        return $this->belongsTo('App\CarePlanTemplate');
    }

    public function patient()
    {
        return $this->belongsTo(User::class,'patient_id');
    }

    public function getCarePlanTemplateIdAttribute()
    {
        //@todo: pretty sure that's not the way it's done. come back here later
        return $this->attributes['care_plan_template_id'];
    }
    //To add functions to get user values

}

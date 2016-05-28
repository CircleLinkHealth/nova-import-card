<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientCarePlan extends Model {

    public function careplantemplate()
    {
        return $this->belongsTo('App\CarePlanTemplate');
    }

    public function patient()
    {
        return $this->belongsTo('App\User','patient_id');
    }

    //To add functions to get user values

}

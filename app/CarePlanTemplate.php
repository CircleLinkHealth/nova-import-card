<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class CarePlanTemplate extends Model {

    protected $fillable = ['program_id', 'display_name'];

    public function program()
    {
        return $this->belongsTo('App\WpBlog', 'program_id');
    }

    public function careplan()
    {
        return $this->hasOne('App\PatientCarePlan','care_plan_template_id');
    }

}

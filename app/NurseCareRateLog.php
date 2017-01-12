<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NurseCareRateLog extends Model
{

    protected $table = 'nurse_care_rate_logs';

    protected $fillable = ['nurse_id', 'activity_id', 'ccm_type', 'increment', 'patient_performed_on'];

    public function nurse(){

        return $this->belongsTo(Nurse::class, 'nurse_id');

    }

    public function activity(){

        return $this->belongsTo(Activity::class, 'activity_id');


    }

}

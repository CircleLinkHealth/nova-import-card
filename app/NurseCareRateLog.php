<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NurseCareRateLog extends \App\BaseModel
{

    protected $table = 'nurse_care_rate_logs';

    protected $fillable = ['nurse_id', 'activity_id', 'ccm_type', 'increment', 'created_at'];

    public function nurse()
    {

        return $this->belongsTo(Nurse::class, 'nurse_id');
    }

    public function activity()
    {

        return $this->belongsTo(Activity::class, 'activity_id');
    }
}

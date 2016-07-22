<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientMonthlySummary extends Model
{
   
    protected $table = 'patient_monthly_summaries';

    protected $guarded = ['id'];

    public function patient_info()
    {
        return $this->belongsTo(PatientInfo::class);
    }

}

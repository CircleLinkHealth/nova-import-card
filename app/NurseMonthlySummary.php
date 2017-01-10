<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class NurseMonthlySummary extends Model
{

    protected $fillable = [
        'nurse_id',
        'month_year',
        'accrued_after_ccm',
        'accrued_towards_ccm',
        'no_of_calls',
        'no_of_successful_calls'
    ];

    public function nurse()
    {

        $this->belongsTo(Nurse::class, 'id', 'nurse_id');

    }
}

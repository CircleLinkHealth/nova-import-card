<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CareAmbassadorLog extends Model
{

    protected $fillable  = [
        'care_ambassador_id',
        'month-year',
        'no_enrolled',
        'no_rejected',
        'no_utc',
        'total_calls',
        'total_time_in_system'
    ];

    public function user(){

        return $this->belongsTo(User::class, 'care_ambassador_id');

    }

    public function totalUniquePatientsCalled(){

        return Enrollee::where('care_ambassador_id', $this->care_ambassador_id)->count();

    }


}

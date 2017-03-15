<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CareAmbassadorLog extends Model
{

    protected $fillable  = [
        'care_ambassador_id',
        'day',
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

    public static function createOrGetLogs($care_ambassador_id){

        $date = Carbon::now()->format('Y-m-d');
        $report =
                self
                    ::where('care_ambassador_id', $care_ambassador_id)
                    ->where('day', $date)
                    ->first();

        if($report == null){

            return self
                   ::create([
                        'care_ambassador_id' => $care_ambassador_id,
                        'day' => $date,
                   ]);

        }

        return $report;

    }


}

<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CareAmbassadorLog extends Model
{

    protected $fillable  = [
        'enroller_id',
        'day',
        'no_enrolled',
        'no_rejected',
        'no_utc',
        'total_calls',
        'total_time_in_system'
    ];

    public function enroller(){

        return $this->belongsTo(CareAmbassador::class, 'enroller_id');

    }

//    public function totalUniquePatientsCalled(){
//
//        return Enrollee::where('care_ambassador_id', $this->care_ambassador_id)->count();
//
//    }

    public static function createOrGetLogs($enroller_id){

        $date = Carbon::now()->format('Y-m-d');
        $report =
                self
                    ::where('enroller_id', $enroller_id)
                    ->where('day', $date)
                    ->first();

        if($report == null){

            return self
                   ::create([
                        'enroller_id' => $enroller_id,
                        'day' => $date,
                   ]);

        }

        return $report;

    }


}

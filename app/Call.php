<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Call extends Model
{



    protected $table = 'calls';

    protected $fillable = [
        'note_id',
        'service',
        'status',
            // reached -> Successful Clinical Call
            // not reached -> Unsuccessful attempt
            // scheduled -> Call to be made

        'inbound_phone_number',
        'outbound_phone_number',

        'inbound_cpm_id',
        'outbound_cpm_id',

        'call_time',
        'created_at',

        'call_date',
        'window_start',
        'window_end',

        'is_cpm_outbound'
    ];

    public function note()
    {
        return $this->belongsTo('App\Note', 'note_id', 'id');
    }

    public function outboundUser()
    {
        return $this->belongsTo('App\User', 'outbound_cpm_id', 'ID');
    }

    public function inboundUser()
    {
        return $this->belongsTo('App\User', 'inbound_cpm_id', 'ID');
    }

    public static function numberOfCallsForPatientForMonth(User $patient, $date){

        $date_start = Carbon::parse($date)->startOfMonth();
        $date_end = Carbon::parse($date)->endOfMonth();

        $no_of_calls = Call::where('outbound_cpm_id', $patient->user_id)
            ->orWhere('inbound_cpm_id', $patient->user_id)
            ->where('created_at', '<=' , $date_end)
            ->where('created_at', '>=' , $date_start)->count();

        return $no_of_calls;
    }

    public static function numberOfSuccessfulCallsForPatientForMonth(User $patient, $date){

        $date_start = Carbon::parse($date)->startOfMonth();
        $date_end = Carbon::parse($date)->endOfMonth();

        $no_of_successful_calls = Call::where('status','reached')->where(
            function ($q) use ($patient){
                $q->where('outbound_cpm_id', $patient->user_id)
                    ->orWhere('inbound_cpm_id', $patient->user_id);
            })
            ->where('created_at', '<=' , $date_end)
            ->where('created_at', '>=' , $date_start)->count();

        return $no_of_successful_calls;
    }


}

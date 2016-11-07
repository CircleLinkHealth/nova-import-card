<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Call extends Model
{

    use \Venturecraft\Revisionable\RevisionableTrait;

    protected $table = 'calls';
    protected $fillable = [
        'note_id',
        'service',
        'status',

        'scheduler',

        /*
        Mini-documentation for call statuses:
            reached -> Successful Clinical Call
            not reached -> Unsuccessful attempt
            scheduled -> Call to be made
            dropped -> call was missed
         */

        'inbound_phone_number',
        'outbound_phone_number',

        'attempt_note',

        'inbound_cpm_id',
        'outbound_cpm_id',

        'call_time',
        'created_at',

        'called_date',
        'scheduled_date',

        'window_start',
        'window_end',

        'is_cpm_outbound'
    ];

    public static function boot()
    {
        parent::boot();
    }

    public static function numberOfCallsForPatientForMonth(User $user, $date){

        // get record for month
        $day_start = Carbon::parse(Carbon::now()->firstOfMonth())->format('Y-m-d');
        $record = $user->patientInfo->patientSummaries()->where('month_year',$day_start)->first();
        if(!$record) {
            return 0;
        }
        return $record->no_of_calls;
    }

    public static function numberOfSuccessfulCallsForPatientForMonth(User $user, $date){

        // get record for month
        $day_start = Carbon::parse(Carbon::now()->firstOfMonth())->format('Y-m-d');
        $record = $user->patientInfo->patientSummaries()->where('month_year',$day_start)->first();
        if(!$record) {
            return 0;
        }
        return $record->no_of_successful_calls;

    }

    public function note()
    {
        return $this->belongsTo('App\Note', 'note_id', 'id');
    }

    public function outboundUser()
    {
        return $this->belongsTo('App\User', 'outbound_cpm_id', 'id');
    }

    public function inboundUser()
    {
        return $this->belongsTo('App\User', 'inbound_cpm_id', 'id');
    }


}

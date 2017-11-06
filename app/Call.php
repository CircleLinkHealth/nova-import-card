<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Call
 *
 * @property int $id
 * @property int|null $note_id
 * @property string $service
 * @property string $status
 * @property string $inbound_phone_number
 * @property string $outbound_phone_number
 * @property int $inbound_cpm_id
 * @property int|null $outbound_cpm_id
 * @property int|null $call_time
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property int $is_cpm_outbound
 * @property string $window_start
 * @property string $window_end
 * @property string $scheduled_date
 * @property string|null $called_date
 * @property string $attempt_note
 * @property string|null $scheduler
 * @property-read \App\User $inboundUser
 * @property-read \App\Note|null $note
 * @property-read \App\User|null $outboundUser
 * @property-read \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereAttemptNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereCallTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereCalledDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereInboundCpmId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereInboundPhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereIsCpmOutbound($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereNoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereOutboundCpmId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereOutboundPhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereScheduledDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereScheduler($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereService($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereWindowEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereWindowStart($value)
 * @mixin \Eloquent
 */
class Call extends \App\BaseModel
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

    public static function numberOfCallsForPatientForMonth(User $user, $date)
    {

        if (!$user->patientInfo) {
            $user->patientInfo()->create([]);
            return;
        }

        // get record for month
        $day_start = Carbon::parse(Carbon::now()->firstOfMonth())->format('Y-m-d');
        $record = $user->patientInfo->monthlySummaries()->where('month_year', $day_start)->first();
        if (!$record) {
            return 0;
        }
        return $record->no_of_calls;
    }

    public static function numberOfSuccessfulCallsForPatientForMonth(User $user, $date)
    {

        if (!$user->patientInfo) {
            $user->patientInfo()->create([]);
            return;
        }

        // get record for month
        $day_start = Carbon::parse(Carbon::now()->firstOfMonth())->format('Y-m-d');
        $record = $user->patientInfo->monthlySummaries()->where('month_year', $day_start)->first();
        if (!$record) {
            return 0;
        }
        return $record->no_of_successful_calls;
    }

    public function note()
    {
        return $this->belongsTo(Note::class, 'note_id', 'id');
    }

    public function outboundUser()
    {
        return $this->belongsTo(User::class, 'outbound_cpm_id');
    }

    public function inboundUser()
    {
        return $this->belongsTo(User::class, 'inbound_cpm_id', 'id');
    }
}

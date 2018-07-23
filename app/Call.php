<?php

namespace App;

use App\Filters\Filterable;
use Carbon\Carbon;

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
 * @property bool $is_from_care_center
 * @property bool is_manual
 * @property-read \App\User|null $schedulerUser
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

    use Filterable,
        \Venturecraft\Revisionable\RevisionableTrait;

    protected $table = 'calls';

    protected $appends = ['is_from_care_center'];

    protected $fillable = [
        'note_id',
        'service',
        'status',

        'scheduler',
        'is_manual',

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

        'is_cpm_outbound',
    ];

    //patient was reached
    const REACHED = 'reached';

    //patient was not reached
    const NOT_REACHED = 'not reached';

    //patient was reached/not reached but this call is to be ignored
    //eg. patient was reached but was busy, so ignore call from reached/not reached reports
    const IGNORED = 'ignored';

    public function getIsFromCareCenterAttribute() {

        if (!is_a($this->schedulerUser, User::class)) {
            //null in cases of scheduler = 'algorithm'
            return false;
        }

        return $this->schedulerUser->hasRole('care-center');
    }

    public static function numberOfCallsForPatientForMonth(User $user, $date)
    {
        if ($date) {
            $d = Carbon::parse($date);
        } else {
            $d = Carbon::now();
        }

        // get record for month
        $day_start = $d->startOfMonth()->toDateString();
        $record    = PatientMonthlySummary::where('month_year', $day_start)->where('patient_id', $user->id)->first();
        if ( ! $record) {
            return 0;
        }

        return $record->no_of_calls;
    }

    public static function numberOfSuccessfulCallsForPatientForMonth(User $user, $date)
    {
        if ($date) {
            $d = Carbon::parse($date);
        } else {
            $d = Carbon::now();
        }

        $calls = Call::where(function ($q) use ($user, $d) {
            $q->where('outbound_cpm_id', $user->id)
              ->orWhere('inbound_cpm_id', $user->id);
        })
                     ->where('called_date', '>=', $d->startOfMonth()->toDateTimeString())
                     ->where('called_date', '<=', $d->endOfMonth()->toDateTimeString())
                     ->where('status', 'reached');

        return $calls->count();
    }

    public function schedulerUser() {
        return $this->belongsTo(User::class, 'scheduler', 'id');
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



    public function patientId()
    {
        return $this->has('outboundUser.patientInfo.user')
            ? $this->outbound_cpm_id
            : $this->inbound_cpm_id;
    }

    /**
     * Scope for calls for the given month
     *
     * @param $builder
     * @param Carbon $monthYear
     *
     */
    public function scopeOfMonth($builder, Carbon $monthYear)
    {
        $builder->whereBetween('called_date', [
            $monthYear->startOfMonth()->toDateTimeString(),
            $monthYear->copy()->endOfMonth()->toDateTimeString(),
        ]);
    }

    /**
     * Scope for status
     *
     * @param $builder
     * @param $status
     *
     */
    public function scopeOfStatus($builder, $status)
    {
        if ( ! is_array($status)) {
            $status = [$status];
        }

        $builder->whereIn('status', $status);
    }

    /**
     * Scope for Scheduled calls for the given month
     *
     * @param $builder
     */
    public function scopeScheduled($builder)
    {
        $builder->where(function ($q) {
            $q->where('calls.status', '=', 'scheduled')
              ->whereHas('inboundUser');
        })->with([
            'inboundUser.billingProvider.user',
            'inboundUser.notes'            => function ($q) {
                $q->latest();
            },
            'inboundUser.patientInfo.contactWindows',
            'inboundUser.patientSummaries' => function ($q) {
                $q->where('month_year', '=', Carbon::now()->startOfMonth()->format('Y-m-d'));
            },
            'inboundUser.primaryPractice',
            'outboundUser.nurseInfo',
            'note',
        ]);
    }
}

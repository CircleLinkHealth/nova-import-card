<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use App\Contracts\AttachableToNotification;
use App\Traits\NotificationAttachable;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Core\Filters\Filterable;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Problem;

/**
 * App\Call.
 *
 * @property int                                                                                         $id
 * @property int|null                                                                                    $note_id
 * @property string|null                                                                                 $type
 * @property string|null                                                                                 $sub_type
 * @property string                                                                                      $service
 * @property string                                                                                      $status
 * @property string                                                                                      $inbound_phone_number
 * @property string                                                                                      $outbound_phone_number
 * @property int                                                                                         $inbound_cpm_id
 * @property int|null                                                                                    $outbound_cpm_id
 * @property int|null                                                                                    $call_time
 * @property \Carbon\Carbon                                                                              $created_at
 * @property \Carbon\Carbon                                                                              $updated_at
 * @property int                                                                                         $is_cpm_outbound
 * @property string                                                                                      $window_start
 * @property string                                                                                      $window_end
 * @property string                                                                                      $scheduled_date
 * @property string|null                                                                                 $called_date
 * @property string                                                                                      $attempt_note
 * @property string|null                                                                                 $scheduler
 * @property bool                                                                                        $is_from_care_center
 * @property bool                                                                                        $is_manual
 * @property \CircleLinkHealth\Customer\Entities\User|null                                               $schedulerUser
 * @property \CircleLinkHealth\Customer\Entities\User                                                    $inboundUser
 * @property \App\Note|null                                                                              $note
 * @property \CircleLinkHealth\Customer\Entities\User|null                                               $outboundUser
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 *
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
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call filter(\App\Filters\QueryFilters $filters)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call ofMonth(\Carbon\Carbon $monthYear)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call ofStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call scheduled()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereIsManual($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereSubType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereType($value)
 *
 * @property int $asap
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereAsap($value)
 *
 * @property int|null                                                                                                        $revision_history_count
 * @property \CircleLinkHealth\Core\Entities\DatabaseNotification[]|\Illuminate\Notifications\DatabaseNotificationCollection $notifications
 * @property int|null                                                                                                        $notifications_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call calledLastThreeMonths()
 *
 * @property \App\Models\CCD\Problem[]|\Illuminate\Database\Eloquent\Collection $attestedProblems
 * @property int|null                                                           $attested_problems_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Call unassigned()
 */
class Call extends BaseModel implements AttachableToNotification
{
    use Filterable;
    use NotificationAttachable;

    //patient was reached/not reached but this call is to be ignored
    //eg. patient was reached but was busy, so ignore call from reached/not reached reports
    const IGNORED = 'ignored';

    //patient was not reached
    const NOT_REACHED = 'not reached';

    const OTHER = 'other call';

    //patient was reached
    const REACHED   = 'reached';
    const SCHEDULED = 'scheduled';

    const WELCOME = 'welcome call';

    protected $appends = ['is_from_care_center'];

    protected $fillable = [
        'type',
        'sub_type',
        'note_id',
        'service',
        'status',

        'scheduler',
        'is_manual',
        'asap',

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

    public function attachAttestedProblems(array $attestedProblems)
    {
        $summary = PatientMonthlySummary::where('patient_id', $this->inbound_cpm_id)
            ->getCurrent()
            ->first();

        if ( ! $summary) {
            \Log::info('Patient monthly summary not found.', [
                'patient_id' => $this->inbound_cpm_id,
                'month'      => Carbon::now()->startOfMonth()->toDateString(),
            ]);
        }

        $this->attestedProblems()->attach($attestedProblems, [
            'patient_monthly_summary_id' => $summary ? $summary->id : null,
        ]);
    }

    public function attestedProblems()
    {
        return $this->belongsToMany(Problem::class, 'call_problems', 'call_id', 'ccd_problem_id')
            ->withTimestamps();
    }

    public function getIsFromCareCenterAttribute()
    {
        if ( ! is_a($this->schedulerUser, User::class)) {
            //null in cases of scheduler = 'algorithm'
            return false;
        }

        return $this->schedulerUser->isCareCoach();
    }

    public function inboundUser()
    {
        return $this->belongsTo(User::class, 'inbound_cpm_id', 'id');
    }

    /**
     * Mark the Notification this Model is attached to as read.
     *
     * @param $notifiable
     */
    public function markAttachmentNotificationAsRead($notifiable)
    {
        // TODO: Implement markAttachmentNotificationAsRead() method.
    }

    public function note()
    {
        return $this->belongsTo(Note::class, 'note_id', 'id');
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

        $calls = Call::where(function ($q) {
            $q->whereNull('type')
                ->orWhere('type', '=', 'call')
                ->orWhere('sub_type', '=', 'Call Back');
        })
            ->where(function ($q) use ($user, $d) {
                $q->where('outbound_cpm_id', $user->id)
                    ->orWhere('inbound_cpm_id', $user->id);
            })
            ->where('called_date', '>=', $d->startOfMonth()->toDateTimeString())
            ->where('called_date', '<=', $d->endOfMonth()->toDateTimeString())
            ->where('status', 'reached');

        return $calls->count();
    }

    public function outboundUser()
    {
        return $this->belongsTo(User::class, 'outbound_cpm_id');
    }

    public function patientId()
    {
        return $this->has('outboundUser.patientInfo.user')
            ? $this->outbound_cpm_id
            : $this->inbound_cpm_id;
    }

    public function schedulerUser()
    {
        return $this->belongsTo(User::class, 'scheduler', 'id');
    }

    /**
     * Get all calls that happened in the last 3 months.
     *
     * @param $builder
     */
    public function scopeCalledLastThreeMonths($builder)
    {
        $builder->whereNotNull('called_date')
            ->where(
                'called_date',
                '>=',
                Carbon::now()->subMonth(3)->startOfMonth()->startOfDay()
            )
            ->where('called_date', '<=', Carbon::now()->endOfDay());
    }

    /**
     * Scope for calls for the given month.
     *
     * @param $builder
     */
    public function scopeOfMonth($builder, Carbon $monthYear)
    {
        $builder->whereBetween('called_date', [
            $monthYear->startOfMonth()->toDateTimeString(),
            $monthYear->copy()->endOfMonth()->toDateTimeString(),
        ]);
    }

    /**
     * Scope for status.
     *
     * @param $builder
     * @param $status
     */
    public function scopeOfStatus($builder, $status)
    {
        if ( ! is_array($status)) {
            $status = [$status];
        }

        $builder->whereIn('status', $status);
    }

    /**
     * Scope for Scheduled calls for the given month.
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
            'inboundUser.notes' => function ($q) {
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

    public function scopeUnassigned($builder)
    {
        $builder->whereNull('outbound_cpm_id');
    }

    public function shouldSendLiveNotification(): bool
    {
        return $this->outbound_cpm_id !== auth()->id()
            && true === $this->asap
            && 'addendum_response' !== $this->sub_type;
    }
}

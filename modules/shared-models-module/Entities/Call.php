<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use Carbon\Carbon;
use CircleLinkHealth\Core\Contracts\AttachableToNotification;
use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Core\Filters\Filterable;
use CircleLinkHealth\Core\Traits\DateScopesTrait;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\NotificationAttachable;

/**
 * CircleLinkHealth\SharedModels\Entities\Call.
 *
 * @property int                                                                                                             $id
 * @property string|null                                                                                                     $type
 * @property int|null                                                                                                        $note_id
 * @property string|null                                                                                                     $service
 * @property string|null                                                                                                     $status
 * @property string|null                                                                                                     $inbound_phone_number
 * @property string|null                                                                                                     $outbound_phone_number
 * @property int                                                                                                             $inbound_cpm_id
 * @property int|null                                                                                                        $outbound_cpm_id
 * @property int|null                                                                                                        $call_time
 * @property \Illuminate\Support\Carbon|null                                                                                 $created_at
 * @property \Illuminate\Support\Carbon|null                                                                                 $updated_at
 * @property int|null                                                                                                        $is_cpm_outbound
 * @property string|null                                                                                                     $window_start
 * @property string|null                                                                                                     $window_end
 * @property string|null                                                                                                     $scheduled_date
 * @property string|null                                                                                                     $called_date
 * @property string|null                                                                                                     $attempt_note
 * @property string|null                                                                                                     $scheduler
 * @property int|null                                                                                                        $is_manual
 * @property string|null                                                                                                     $sub_type
 * @property int                                                                                                             $asap
 * @property \CircleLinkHealth\SharedModels\Entities\Problem[]|\Illuminate\Database\Eloquent\Collection                      $attestedProblems
 * @property int|null                                                                                                        $attested_problems_count
 * @property \CircleLinkHealth\SharedModels\Entities\CpmCallAlert|null                                                       $cpmCallAlert
 * @property mixed                                                                                                           $is_from_care_center
 * @property User                                                                                                            $inboundUser
 * @property \CircleLinkHealth\SharedModels\Entities\Note|null                                                               $note
 * @property \CircleLinkHealth\Core\Entities\DatabaseNotification[]|\Illuminate\Notifications\DatabaseNotificationCollection $notifications
 * @property int|null                                                                                                        $notifications_count
 * @property User|null                                                                                                       $outboundUser
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection                     $revisionHistory
 * @property int|null                                                                                                        $revision_history_count
 * @property User|null                                                                                                       $schedulerUser
 * @property \CircleLinkHealth\SharedModels\Entities\VoiceCall[]|\Illuminate\Database\Eloquent\Collection                    $voiceCalls
 * @property int|null                                                                                                        $voice_calls_count
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call calledLastThreeMonths()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call createdInMonth(\Carbon\Carbon $date, string $field = 'created_at')
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call createdOn(\Carbon\Carbon $date, string $field = 'created_at')
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call createdOnIfNotNull(?\Carbon\Carbon $date = null, $field = 'created_at')
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call createdThisMonth(string $field = 'created_at')
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call createdToday(string $field = 'created_at')
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call createdYesterday(string $field = 'created_at')
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call filter(\CircleLinkHealth\Core\Filters\QueryFilters $filters)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call newModelQuery()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call newQuery()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call ofMonth(\Carbon\Carbon $monthYear)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call ofStatus($status)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call query()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call scheduled()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call unassigned()
 * @mixin \Eloquent
 */
class Call extends BaseModel implements AttachableToNotification
{
    use DateScopesTrait;
    use Filterable;
    use NotificationAttachable;

    //Denotes a completed task
    const DONE = 'done';

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

    public function attachAttestedProblems(array $attestedProblems, ?int $addendumId = null)
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
            'addendum_id'                => $addendumId,
        ]);
    }

    public function attestedProblems()
    {
        return $this->belongsToMany(Problem::class, 'call_problems', 'call_id', 'ccd_problem_id')
            //todo: add attestation pivot model - maybe we can query directly, or relate that to the patient user directly.
            ->withPivot([
                'chargeable_month',
                'patient_user_id',
                'ccd_problem_name',
                'ccd_problem_icd_10_code',
            ])
            ->withTimestamps();
    }

    public function cpmCallAlert()
    {
        return $this->hasOne(CpmCallAlert::class, 'call_id', 'id');
    }

    public function getIsFromCareCenterAttribute()
    {
        if ( ! $this->schedulerUser instanceof User) {
            //null in cases of scheduler = 'algorithm'
            return false;
        }

        return $this->schedulerUser->isCareCoach();
    }

    public function inboundUser()
    {
        return $this->belongsTo(User::class, 'inbound_cpm_id', 'id');
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

    public static function numberOfSuccessfulCallsForPatientForMonth(int $patientId, Carbon $date): int
    {
        return Call::where(function ($q) {
            $q->whereNull('type')
                ->orWhere('type', '=', 'call')
                ->orWhere('sub_type', '=', 'Call Back');
        })
            ->where(function ($q) use ($patientId) {
                $q->where('outbound_cpm_id', $patientId)
                    ->orWhere('inbound_cpm_id', $patientId);
            })
            ->where('called_date', '>=', $date->startOfMonth()->toDateTimeString())
            ->where('called_date', '<=', $date->endOfMonth()->toDateTimeString())
            ->where('status', Call::REACHED)
            ->count();
    }

    public function outboundUser()
    {
        return $this->belongsTo(User::class, 'outbound_cpm_id');
    }

    public function patientId()
    {
        return $this->has('outboundUser.patientInfo.user')->exists()
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

    public function voiceCalls()
    {
        return $this->hasMany(VoiceCall::class, 'call_id', 'id');
    }
}

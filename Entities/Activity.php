<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TimeTracking\Entities;

use App\CcmTimeApiLog;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\TimeTracking\Traits\DateScopesTrait;
use Illuminate\Support\Facades\DB;

/**
 * CircleLinkHealth\TimeTracking\Entities\Activity.
 *
 * @property int                 $id
 * @property string|null         $type
 * @property int                 $duration
 * @property string|null         $duration_unit
 * @property int                 $patient_id
 * @property int                 $provider_id
 * @property int                 $logger_id
 * @property int                 $comment_id
 * @property bool                $is_behavioral
 * @property int|null            $sequence_id
 * @property string              $obs_message_id
 * @property string              $logged_from
 * @property string              $performed_at
 * @property string              $performed_at_gmt
 * @property \Carbon\Carbon      $created_at
 * @property \Carbon\Carbon      $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property int|null            $page_timer_id
 * @property \CircleLinkHealth\Customer\Entities\NurseCareRateLog[]|\Illuminate\Database\Eloquent\Collection
 *     $careRateLogs
 * @property \App\CcmTimeApiLog                                                                              $ccmApiTimeSentLog
 * @property mixed                                                                                           $performed_at_year_month
 * @property \CircleLinkHealth\TimeTracking\Entities\ActivityMeta[]|\Illuminate\Database\Eloquent\Collection $meta
 * @property \CircleLinkHealth\TimeTracking\Entities\PageTimer                                               $pageTime
 * @property \CircleLinkHealth\Customer\Entities\User                                                        $patient
 * @property \CircleLinkHealth\Customer\Entities\User                                                        $provider
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection     $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|Activity
 *     createdBy(\CircleLinkHealth\Customer\Entities\User $user)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity createdThisMonth($field = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder|Activity createdOn(Carbon $date, $field = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder|Activity createdToday($field = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder|Activity createdYesterday($field = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereDurationUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereLoggedFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereLoggerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereObsMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity wherePageTimerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity wherePerformedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity wherePerformedAtGmt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereSequenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\Activity
 *     createdInMonth(\Carbon\Carbon $date, $field = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\Activity
 *     newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\Activity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\Activity query()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\Activity
 *     whereIsBehavioral($value)
 * @property int|null $care_rate_logs_count
 * @property int|null $meta_count
 * @property int|null $revision_history_count
 */
class Activity extends BaseModel
{
    use DateScopesTrait;

    protected $appends = ['performed_at_year_month'];

    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'duration',
        'duration_unit',
        'patient_id',
        'provider_id',
        'logger_id',
        'comment_id',
        'chargeable_service_id',
        'is_behavioral',
        'logged_from',
        'performed_at',
        'performed_at_gmt',
        'page_timer_id',
        'created_at',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'lv_activities';

    public function careRateLogs()
    {
        return $this->hasMany(NurseCareRateLog::class);
    }

    public function ccmApiTimeSentLog()
    {
        return $this->hasOne(CcmTimeApiLog::class);
    }

    /**
     * Get all activities with all their meta for a given patient.
     *
     * @param $patientId
     *
     * @return mixed
     */
    public function getActivitiesWithMeta($patientId)
    {
        $activities = Activity::where('patient_id', '=', $patientId)->get();

        foreach ($activities as $act) {
            $act['meta'] = $act->meta;
        }

        return $activities;
    }

    public function getActivityCommentFromMeta($id)
    {
        $comment = DB::table('lv_activitymeta')->where('activity_id', $id)->where(
            'meta_key',
            'comment'
        )->pluck('meta_value');

        if ($comment) {
            return $comment;
        }

        return false;
    }

    public function getCommentForActivity()
    {
        return optional($this->meta->where('meta_key', 'comment')->first())->meta_value;
    }

    public function getPerformedAtYearMonthAttribute()
    {
        if ( ! empty($this->attributes['performed_at'])) {
            return Carbon::parse($this->attributes['performed_at'])->format('Y-m');
        }
    }

    /**
     * Returns activity data used to build reports.
     *
     * @param int  $timeLessThan
     * @param bool $range
     *
     * @return bool
     */
    public static function getReportData(
        array $months,
        $timeLessThan = 20,
        array $patientIds = [],
        $range = true
    ) {
        $query = Activity::whereBetween('performed_at', [
            Carbon::createFromFormat('Y-n', $months[0])->startOfMonth(),
            Carbon::createFromFormat('Y-n', $months[1])->endOfMonth(),
        ]);

        ! empty($patientIds)
            ? $query->whereIn('patient_id', $patientIds)
            : '';

        $data = $query
            ->whereIn('patient_id', function ($subQuery) use (
                $timeLessThan
            ) {
                $subQuery->select('patient_id')
                    ->from(with(new Activity())->getTable())
                    ->groupBy('patient_id')
                    //->having(DB::raw('SUM(duration)'), '<', $timeLessThan)
                    ->get();
            })
            ->with('patient')
            ->orderBy('performed_at', 'asc')
            ->get()
            ->groupBy('patient_id');

        /*
         * Using multiple groupBy clauses didn't work.
         * Come back here later.
         */
        foreach ($patientIds as $patientId) {
            $reportData[$patientId] = [];
        }
        foreach ($data as $patientAct) {
            $reportData[$patientAct[0]['patient_id']] = collect($patientAct)->groupBy('performed_at_year_month');
        }

        if ( ! empty($reportData)) {
            return $reportData;
        }

        return false;
    }

    public static function input_activity_types()
    {
        return [
            'CCM Welcome Call'                        => 'CCM Welcome Call',
            'Reengaged'                               => 'Reengaged',
            'General (Clinical)'                      => 'General (Clinical)',
            'Test (Scheduling, Communications, etc)'  => 'Test (Scheduling, Communications, etc)',
            'Call to Other Care Team Member'          => 'Call to Other Care Team Member',
            'Review Care Plan'                        => 'Review Care Plan',
            'Review Patient Progress'                 => 'Review Patient Progress',
            'Transitional Care Management Activities' => 'Transitional Care Management Activities',
            'Other'                                   => 'Other',
        ];
    }

    public function meta()
    {
        return $this->hasMany(ActivityMeta::class);
    }

    public function pageTime()
    {
        return $this->belongsTo(PageTimer::class, 'page_timer_id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id', 'id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id')->withTrashed();
    }

    public function scopeCreatedBy(
        $builder,
        User $user
    ) {
        $builder->where('provider_id', $user->id)
            ->orWhere('logger_id', $user->id);
    }

    public static function task_types_to_topics()
    {
        return [
            'CP Review'  => 'Review Care Plan',
            'Call Back'  => 'Call Back',
            'Refill'     => 'Refill',
            'Send Info'  => 'Send Info',
            'Get Appt.'  => 'Get Appt.',
            'Other Task' => 'Other Task',
        ];
    }

    public static function totalTimeForPatientForMonth(
        Patient $p,
        Carbon $month,
        $format = false
    ) {
        $raw = Activity::where('patient_id', $p->user_id)
            ->where('performed_at', '>', $month->firstOfMonth()->startOfMonth()->toDateTimeString())
            ->where('performed_at', '<', $month->lastOfMonth()->endOfDay()->toDateTimeString())
            ->sum('duration');

        if ($format) {
            return round($raw / 60, 2);
        }

        return $raw;
    }
    
    public function chargeableService(){
        return $this->belongsTo(ChargeableService::class, 'chargeable_service_id');
    }
}

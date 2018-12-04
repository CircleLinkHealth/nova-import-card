<?php namespace App;

use App\Scopes\Universal\DateScopesTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * App\Activity
 *
 * @property int $id
 * @property string|null $type
 * @property int $duration
 * @property string|null $duration_unit
 * @property int $patient_id
 * @property int $provider_id
 * @property int $logger_id
 * @property int $comment_id
 * @property boolean $is_behavioral
 * @property int|null $sequence_id
 * @property string $obs_message_id
 * @property string $logged_from
 * @property string $performed_at
 * @property string $performed_at_gmt
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property int|null $page_timer_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\NurseCareRateLog[] $careRateLogs
 * @property-read \App\CcmTimeApiLog $ccmApiTimeSentLog
 * @property-read mixed $performed_at_year_month
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ActivityMeta[] $meta
 * @property-read \App\PageTimer $pageTime
 * @property-read \App\User $patient
 * @property-read \App\User $provider
 * @property-read \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity createdBy(\App\User $user)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity createdThisMonth($field = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity createdOn(Carbon $date, $field = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity createdToday($field = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity createdYesterday($field = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereDurationUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereLoggedFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereLoggerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereObsMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity wherePageTimerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity wherePerformedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity wherePerformedAtGmt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereSequenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Activity extends BaseModel implements Transformable
{
    use DateScopesTrait, TransformableTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'lv_activities';

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
        'is_behavioral',
        'logged_from',
        'performed_at',
        'performed_at_gmt',
        'page_timer_id',
        'created_at',
    ];

    protected $dates = ['deleted_at'];

    protected $appends = ['performed_at_year_month'];

    /**
     * Create a new activity and return its id
     *
     * @param $attr
     *
     * @return mixed
     */
    public static function createNewActivity($attr)
    {
        $newActivity = Activity::create($attr);

        return $newActivity->id;
    }

    /**
     * Returns activity data used to build reports
     *
     * @param array $months
     * @param int $timeLessThan
     * @param array $patientIds
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
                         ->from(with(new Activity)->getTable())
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

        if (! empty($reportData)) {
            return $reportData;
        } else {
            return false;
        }
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

    public function getCommentForActivity()
    {
        return $this->meta->where('meta_key', 'comment')->first()->meta_value;
    }

    public function getPerformedAtYearMonthAttribute()
    {
        if (! empty($this->attributes['performed_at'])) {
            return Carbon::parse($this->attributes['performed_at'])->format('Y-m');
        }
    }

    public function meta()
    {
        return $this->hasMany(ActivityMeta::class);
    }

    public function careRateLogs()
    {
        return $this->hasMany(NurseCareRateLog::class);
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id', 'id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id')->withTrashed();
    }

    public function pageTime()
    {
        return $this->belongsTo(PageTimer::class, 'page_timer_id');
    }

    public function ccmApiTimeSentLog()
    {
        return $this->hasOne(CcmTimeApiLog::class);
    }

    /**
     * Get all activities with all their meta for a given patient
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
        } else {
            return false;
        }
    }

    public function scopeCreatedBy(
        $builder,
        User $user
    ) {
        $builder->where('provider_id', $user->id)
                ->orWhere('logger_id', $user->id);
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
}

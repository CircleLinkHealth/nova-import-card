<?php namespace App;

use App\Scopes\Universal\DateScopesTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;
use SoftDeletingTrait;

class Activity extends Model implements Transformable
{

    use DateScopesTrait, TransformableTrait;

    // for revisionable
    use \Venturecraft\Revisionable\RevisionableTrait;
    protected $revisionCreationsEnabled = true;

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql_no_prefix';

    /**
     * The database table used by the model.
     * @SWG\Property()
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
        'logged_from',
        'performed_at',
        'performed_at_gmt',
        'page_timer_id',
        'created_at',
    ];

    protected $dates = ['deleted_at'];

    protected $appends = ['performed_at_year_month'];

    public static function boot()
    {
        parent::boot();
    }

    // for revisionable

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

        !empty($patientIds)
            ? $query->whereIn('patient_id', $patientIds)
            : '';

        $data = $query
            ->whereIn('patient_id', function ($subQuery) use
            (
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

        if (!empty($reportData)) {
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

    public function getCommentForActivity()
    {
        return $this->meta->where('meta_key', 'comment')->first()->meta_value;
    }

    public function getPerformedAtYearMonthAttribute()
    {
        if (!empty($this->attributes['performed_at'])) {
            return Carbon::parse($this->attributes['performed_at'])->format('Y-m');
        }
    }

    public function meta()
    {
        return $this->hasMany('App\ActivityMeta');
    }

    public function patient()
    {
        return $this->belongsTo('App\User', 'patient_id', 'id');
    }

    public function pageTime()
    {
        return $this->belongsTo('App\PageTimer');
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
        $comment = DB::table('lv_activitymeta')->where('activity_id', $id)->where('meta_key',
            'comment')->pluck('meta_value');

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


}

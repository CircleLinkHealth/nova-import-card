<?php namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Activity extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type', 'duration', 'duration_unit', 'patient_id', 'provider_id', 'logger_id',
        'logged_from', 'performed_at', 'performed_at_gmt'];

    protected $dates = ['deleted_at'];

    protected $appends = ['performed_at_year_month'];

    public function getPerformedAtYearMonthAttribute()
    {
        if ( !empty( $this->attributes['performed_at'] ) ) {
            return Carbon::parse($this->attributes['performed_at'])->format('Y-m');
        }
    }

    public function meta()
    {
        return $this->hasMany('App\ActivityMeta');
    }


    /**
     * Create a new activity and return its id
     *
     * @param $attr
     * @return mixed
     */
    public static function createNewActivity($attr)
    {
        $newActivity = Activity::create($attr);

        return $newActivity->id;
    }


    /**
     * Get all activities with all their meta for a given patient
     *
     * @param $patientId
     * @return mixed
     */
    public function getActivitiesWithMeta($patientId)
    {
        $activities = Activity::where('patient_id', '=', $patientId)->get();

        foreach ( $activities as $act )
        {
            $act['meta'] = $act->meta;
        }

        return $activities;
    }


    /**
     * Returns activity data used to build reports
     *
     * @param array $months
     * @param int $timeLessThan
     * @param array $patientIds
     * @param bool $range
     * @return bool
     */
    public static function getReportData(array $months, $timeLessThan = 20, array $patientIds = [], $range = true)
    {
        $query = Activity::whereBetween('performed_at', [
            Carbon::createFromFormat('Y-n', $months[0])->startOfMonth(),
            Carbon::createFromFormat('Y-n', $months[1])->endOfMonth()
        ]);

        !empty($patientIds) ? $query->whereIn('patient_id', $patientIds) : '';

        $data = $query
            ->whereIn('patient_id', function($subQuery) use ($timeLessThan){
                $subQuery->select('patient_id')
                    ->from( with(new Activity)->getTable() )
                    ->groupBy('patient_id')
                    ->having(DB::raw('SUM(duration)'), '<', $timeLessThan)
                    ->get();
            })
            ->orderBy('performed_at', 'asc')
            ->get()
            ->groupBy('patient_id');

        /*
         * Using multiple groupBy clauses didn't work.
         * Come back here later.
         */
        foreach ($data as $patientAct)
        {
            $reportData[$patientAct[0]['patient_id']] = collect($patientAct)->groupBy('performed_at_year_month');
        }

        if(!empty($reportData)) {
            return $reportData;
        } else {
            return false;
        }
    }


}

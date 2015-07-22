<?php namespace App;

use App\WpUser;
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
        'logged_from', 'performed_at', 'performed_at_gmt', 'page_timer_id'];

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


    public function patient()
    {
        return $this->belongsTo('App\WpUser', 'patient_id', 'ID');
    }

    public function pageTime()
    {
        return $this->belongsTo('App\PageTimer', 'page_timer_id', 'id');
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

    public function getActivityCommentFromMeta($id)
    {
        $comment = DB::table('activitymeta')->where('activity_id',$id)->where('meta_key','comment')->pluck('meta_value');

        return $comment;
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
        foreach($patientIds as $patientId) {
            $reportData[$patientId] = array();
        }
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

    public function getTotalActivityTimeForMonth($userId, $month = false) {
        // if no month, set to current month
        if(!$month) {
            $month =  date('m');
        }
        $totalDuration = Activity::where( DB::raw('MONTH(created_at)'), '=', $month )->where( 'patient_id', '=', $userId )->sum('duration');
        return $totalDuration;
    }

    public function reprocessMonthlyActivityTime($userIds = false, $month = false) {
        // if no month, set to current month
        if(!$month) {
            $month =  date('m');
        }

        if($userIds) {
            // cast userIds to array if string
            if(!is_array($userIds)) {
                $userIds = array($userIds);
            }
            $wpUsers = wpUser::whereIn('id', $userIds)->orderBy('ID', 'desc')->get();
        } else {
            // get all users
            $wpUsers = wpUser::orderBy('ID', 'desc')->get();
        }

        if(!empty($wpUsers)) {
            // loop through each user
            foreach($wpUsers as $wpUser) {
                // get all activities for user for month
                $totalDuration = $this->getTotalActivityTimeForMonth($month, $wpUser->ID);

                // update user_meta with total
                $userMeta = WpUserMeta::where('user_id', '=', $wpUser->ID)
                    ->where('meta_key', '=', 'cur_month_activity_time')->first();
                if(!$userMeta) {
                    // add in initial user meta: cur_month_activity_time
                    $newUserMetaAttr = array(
                        'user_id' => $wpUser->ID,
                        'meta_key' => 'cur_month_activity_time',
                        'meta_value' => $totalDuration,
                    );
                    $newUserMeta = WpUserMeta::create($newUserMetaAttr);
                    //echo "<pre>CREATED";var_dump($newUserMeta);echo "</pre>";die();
                } else {
                    // update existing user meta: cur_month_activity_time
                    $userMeta = WpUserMeta::where('user_id', '=', $wpUser->ID)
                        ->where('meta_key', '=', 'cur_month_activity_time')
                        ->update(array('meta_value' => $totalDuration));
                    //echo "<pre>UPDATED";var_dump($totalDuration);echo "</pre>";die();
                }
            }
        }
        return true;
    }


}

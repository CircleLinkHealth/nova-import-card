<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type', 'duration', 'duration_unit', 'patient_id', 'provider_id', 'logger_id',
        'logged_from', 'performed_at', 'performed_at_gmt'];

    protected $dates = ['deleted_at'];

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
     * @param $patient_id
     * @return mixed
     */
    public function getActivitiesWithMeta($patient_id)
    {
        $activities = Activity::where('patient_id', '=', $patient_id)->get();

        foreach ( $activities as $act )
        {
            $act['meta'] = $act->meta;
        }

        return $activities;
    }

//    Still working here
//    public function getTimeReport(array $months, array $users = [], $time = null, $range = false)
//    {
//        if ( !empty($users) )
//        {
//            Activity::select( DB::raw('user_id, sum(act_value)') )->get()
//            Activity::whereIn('user_id', $users)->get();
//        }
//
//    }

}

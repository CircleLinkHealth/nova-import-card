<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'ma_activities';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'act_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['act_date', 'user_id', 'performed_by', 'act_method', 'act_key', 'act_value', 'act_unit'];

    protected $dates = ['deleted_at'];

    public function meta()
    {
        return $this->hasMany('App\ActivityMeta', 'act_id');
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

        return $newActivity->act_id;
    }


    public function getActivitiesWithMeta($user_id)
    {
        $activities = Activity::where('user_id', '=', $user_id)->get();

        foreach ( $activities as $act )
        {
            $act['meta'] = $act->meta;
        }

        return $activities;
    }

}

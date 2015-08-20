<?php namespace App;

use Illuminate\Database\Eloquent\Model;
/**
 * @SWG\Definition(required={"primaryKey"})
 */

class Observation extends Model {

    /**
     * The connection name for the model.
     * @SWG\Property()
     * @var string
     */
    protected $connection = 'mysql_no_prefix';

    /**
     * The database table used by the model.
     * @SWG\Property()
     * @var string
     */
    protected $table = 'ma_X_observations';

    /**
     * The primary key for the model.
     *@SWG\Property()
     * @var string
     */
    protected $primaryKey = 'obs_id';

    /**
     * The attributes that are mass assignable.
     *@SWG\Property()
     * @var array
     */
    protected $fillable = ['obs_id', 'obs_date', 'obs_date_gmt', 'comment_id', 'sequence_id', 'obs_message_id', 'user_id', 'obs_method', 'obs_key', 'obs_value', 'obs_unit'];

    protected $dates = ['obs_date', 'obs_date_gmt'];

    public $timestamps = false;

    public function comment()
    {
        return $this->belongsTo('App\Comment');
    }

    public function meta()
    {
        return $this->hasMany('App\ObservationMeta', 'user_id', 'ID');
    }


    public function getObservation($obs_id)
    {
        $observation = Observation::where('obs_id', '=', $obs_id)->get();
        return $observation;
    }


    public function getObservationsForUser($user_id)
    {
        $observations = Observation::where('user_id', '=', $user_id)->get();

        foreach ( $observations as $observation )
        {
            $observation['meta'] = $observation->meta;
        }

        return $observations;
    }



}

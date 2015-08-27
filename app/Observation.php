<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use App\WpUser;
use App\WpUserMeta;
use DB;

/**
 * @SWG\Definition(required={"primaryKey"})
 */

class Observation extends Model {

    /**
     * The connection name for the model.
     * @SWG\Property()
     * @var string
     */
    //protected $connection = 'mysql_no_prefix';

    /**
     * The database table used by the model.
     * @SWG\Property()
     * @var string
     */
    protected $table = 'observations';

    /**
     * The primary key for the model.
     *@SWG\Property()
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *@SWG\Property()
     * @var array
     */
    protected $fillable = ['obs_date', 'obs_date_gmt', 'comment_id', 'sequence_id', 'obs_message_id', 'user_id', 'obs_method', 'obs_key', 'obs_value', 'obs_unit', 'program_id', 'legacy_obs_id'];

    protected $dates = ['deleted_at'];

    public $timestamps = true;

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

    public function save(array $params = array())
    {
        if(empty($this->user_id)) {
            return false;
        }
        $wpUser = WpUser::find($this->user_id);
        if(!$wpUser->blogId()) {
            return false;
        }
        $comment = Comment::find($this->comment_id);
        if(!$comment) {
            return false;
        }

        // take programId(blogId) and add to wp_X_observations table
        $params['comment_id'] = $comment->legacy_comment_id;
        $params['user_id'] = $this->user_id;
        $params['obs_date'] = $this->obs_date;
        $params['obs_date_gmt'] = $this->obs_date_gmt;
        $params['sequence_id'] = $this->sequence_id;
        $params['obs_message_id'] = $this->obs_message_id;
        $params['obs_method'] = $this->obs_method;
        $params['obs_key'] = $this->obs_key;
        $params['obs_value'] = $this->obs_value;
        $params['obs_unit'] = $this->obs_unit;
        $this->program_id = $wpUser->blogId();

        // updating or inserting?
        if($this->id) {
            DB::connection('mysql_no_prefix')->table('ma_'.$wpUser->blogId().'_observations')->where('comment_ID', $this->legacy_obs_id)->update($params);
        } else {
            $resultObsId = DB::connection('mysql_no_prefix')->table('ma_'.$wpUser->blogId().'_observations')->insertGetId($params);
            $this->legacy_obs_id = $resultObsId;
        }

        parent::save();
        // http://www.amitavroy.com/justread/content/articles/events-laravel-5-and-customize-model-save
    }


}

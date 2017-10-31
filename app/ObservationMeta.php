<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ObservationMeta extends Model
{

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql_no_prefix';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'lv_observationmeta';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'obs_id', 'comment_id', 'message_id', 'legacy_meta_id', 'meta_key', 'meta_value'];

    public function observationMeta()
    {
        return $this->belongsTo('App\Observation', 'obs_id');
    }


    public function save(array $params = array())
    {
        if (empty($this->obs_id)) {
            return false;
        }
        $observation = Observation::find($this->obs_id);
        $comment = Comment::find($observation->comment_id);
        $wpUser = User::find($observation->user_id);

        if (!$wpUser || !$observation) {
            return false;
        }

        /*
         * // NO LONGER NEEDED IN 3.0, REMOVING LEGACY MA_* TABLES
         *
        // take programId(primaryProgramId) and add to wp_X_observationmeta table
        $params['obs_id'] = $observation->legacy_obs_id;
        if($comment) {
            $params['comment_id'] = $comment->legacy_comment_id;
        } else {
            $this->comment_id = '0';
            $params['comment_id'] = '0';
        }
        $params['message_id'] = $this->message_id;
        $params['meta_key'] = $this->meta_key;
        $params['meta_value'] = $this->meta_value;
        $this->program_id = $wpUser->primaryProgramId();

        // updating or inserting?
        if($this->id) {
            DB::connection('mysql_no_prefix')->table('ma_'.$wpUser->primaryProgramId().'_observationmeta')->where('comment_ID', $this->legacy_meta_id)->update($params);
        } else {
            // add to legacy if doesnt already exist
            if(empty($this->legacy_meta_id)) {
                $resultId = DB::connection('mysql_no_prefix')->table('ma_' . $wpUser->primaryProgramId() . '_observationmeta')->insertGetId($params);
                $this->legacy_meta_id = $resultId;
            }
        }
        */

        parent::save();
        // http://www.amitavroy.com/justread/content/articles/events-laravel-5-and-customize-model-save
    }
}

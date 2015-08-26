<?php namespace App;

use App\WpUser;
use App\WpUserMeta;
use Illuminate\Database\Eloquent\Model;
use DB;

class ObservationMeta extends Model {

    /**
     * The connection name for the model.
     *
     * @var string
     */
    //protected $connection = 'mysql_no_prefix';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'observationmeta';

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
        if(empty($this->obs_id)) {
            return false;
        }
        $observation = Observation::find($this->obs_id);
        $wpUser = WpUser::find($observation->user_id);

        if(!$wpUser || !$observation) {
            return false;
        }

        // take programId(blogId) and add to wp_X_observationmeta table
        $params['obs_id'] = $observation->id;
        $params['comment_id'] = $this->comment_id;
        $params['message_id'] = $this->message_id;
        $params['meta_key'] = $this->meta_key;
        $params['meta_value'] = $this->meta_value;
        $resultMetaId =  DB::connection('mysql_no_prefix')->table('ma_'.$wpUser->blogId().'_observationmeta')->insertGetId($params);

        $this->program_id = $wpUser->blogId();
        $this->legacy_meta_id = $resultMetaId;

        parent::save();
        // http://www.amitavroy.com/justread/content/articles/events-laravel-5-and-customize-model-save
    }



}

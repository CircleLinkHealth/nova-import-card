<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ObservationMeta extends Model {

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
        if(empty($this->obs_id)) {
            return false;
        }
        $observation = Observation::find($this->obs_id);
        $comment = Comment::find($observation->comment_id);
        $wpUser = User::find($observation->user_id);

        if(!$wpUser || !$observation) {
            return false;
        }

        parent::save();
        // http://www.amitavroy.com/justread/content/articles/events-laravel-5-and-customize-model-save
    }



}

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
    protected $table = 'ma_7_observationmeta';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'meta_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['meta_id', 'obs_id', 'comment_id', 'message_id', 'meta_key', 'meta_value'];

    public function observationMeta()
    {
        return $this->belongsTo('App\Observation', 'obs_id');
    }



}

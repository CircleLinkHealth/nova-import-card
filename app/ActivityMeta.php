<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityMeta extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'ma_activity_meta';

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
    protected $fillable = ['act_id', 'meta_key', 'meta_value'];

}

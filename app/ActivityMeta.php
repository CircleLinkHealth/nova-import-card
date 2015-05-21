<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityMeta extends Model {

    use SoftDeletes;

    protected $table = 'activitymeta';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['activity_id', 'meta_key', 'meta_value'];

    protected $dates = ['deleted_at'];

    public function activity()
    {
        return $this->belongsTo('App\Activity');
    }



}

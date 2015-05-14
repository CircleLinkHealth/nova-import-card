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

}

<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class PageTimer extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'page_timer';

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
    protected $fillable = ['duration', 'duration_unit', 'patient_id', 'provider_id', 'start_time', 'start_time_gmt', 'end_time', 'end_time_gmt', 'url_full', 'url_short', 'program_id'];

    protected $dates = ['deleted_at'];

}

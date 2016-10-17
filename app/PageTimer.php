<?php namespace App;

use App\Scopes\Universal\DateScopesTrait;
use Illuminate\Database\Eloquent\Model;

class PageTimer extends Model
{
    use DateScopesTrait;

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql_no_prefix';

    /**
     * The database table used by the model.
     * @SWG\Property()
     * @var string
     */
    protected $table = 'lv_page_timer';

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
    protected $fillable = [
        'billable_duration',
        'duration',
        'duration_unit',
        'patient_id',
        'provider_id',
        'start_time',
        'actual_start_time',
        'end_time',
        'actual_end_time',
        'url_full',
        'url_short',
        'program_id',
    ];

    protected $dates = ['deleted_at'];

    public function logger()
    {
        return $this->belongsTo('App\User', 'provider_id', 'ID');
    }

    public function patient()
    {
        return $this->belongsTo('App\User', 'patient_id', 'ID');
    }

    public function rule()
    {
        return $this->belongsTo('App\Rules');
    }

    public function activities()
    {
        return $this->hasMany('App\Activity', 'page_timer_id');
    }
}

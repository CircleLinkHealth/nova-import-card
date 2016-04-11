<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientCarePlan extends Model {

    use SoftDeletes;

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'provider_info';

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
    protected $fillable = ['user_id', 'qualification', 'npi_number', 'specialty'];

    public $timestamps = false;

    // START RELATIONSHIPS

    // user
    public function user()
    {
        return $this->belongsTo('App\User', 'ID', 'user_id');
    }
    // END RELATIONSHIPS

}

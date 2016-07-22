<?php namespace App;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;

class PatientContactWindow extends Model {

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
	protected $table = 'patient_contact_window';

	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primaryKey = 'id';

	public $timestamps = false;

	// START RELATIONSHIPS

	// user
	public function user()
	{
		return $this->belongsTo('App\User', 'ID', 'user_id');
	}

	// END RELATIONSHIPS


}

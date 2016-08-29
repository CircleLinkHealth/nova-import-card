<?php namespace App;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;

class PatientInfo extends Model {

	use SoftDeletes;
	use \Venturecraft\Revisionable\RevisionableTrait;

	public static function boot()
	{
		parent::boot();
	}

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
	protected $table = 'patient_info';

	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primaryKey = 'id';

	protected $guarded = [];

	// START RELATIONSHIPS

	// user
	public function user()
	{
		return $this->belongsTo('App\User', 'ID', 'user_id');
	}

	// END RELATIONSHIPS



	// START ATTRIBUTES

	// first_name
	public function getFirstNameAttribute() {
		return $this->user->first_name;
	}
	public function setFirstNameAttribute($value) {
		$this->user->first_name = $value;
		$this->user->save();
		return true;
	}

	// last_name
	public function getLastNameAttribute() {
		return $this->user->last_name;
	}
	public function setLastNameAttribute($value) {
		$this->user->last_name = $value;
		$this->user->save();
		return true;
	}

	// address
	public function getAddressAttribute() {
		return $this->user->address;
	}
	public function setAddressAttribute($value) {
		$this->user->address = $value;
		$this->user->save();
		return true;
	}

	// city
	public function getCityAttribute() {
		return $this->user->city;
	}
	public function setCityAttribute($value) {
		$this->user->city = $value;
		$this->user->save();
		return true;
	}

	// state
	public function getStateAttribute() {
		return $this->user->state;
	}
	public function setStateAttribute($value) {
		$this->user->state = $value;
		$this->user->save();
		return true;
	}

	// zip
	public function getZipAttribute() {
		return $this->user->zip;
	}
	public function setZipAttribute($value) {
		$this->user->zip = $value;
		$this->user->save();
		return true;
	}


	public function setCcmStatusAttribute($value)
	{
		$statusBefore = $this->ccm_status;
		$this->attributes['ccm_status'] = $value;
		// update date tracking
		if ($statusBefore !== $value) {
			if ($value == 'paused') {
				$this->attributes['date_paused'] = date("Y-m-d H:i:s");
			};
			if ($value == 'withdrawn') {
				$this->attributes['date_withdrawn'] = date("Y-m-d H:i:s");
			};
		}
		$this->save();
		return true;
	}
	// END ATTRIBUTES
}

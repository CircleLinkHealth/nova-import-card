<?php namespace App;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;

class ProviderInfo extends Model {

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
	protected $fillable = ['user_id', 'prefix', 'qualification', 'npi_number', 'specialty'];

	public $timestamps = false;

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

	// END ATTRIBUTES
}

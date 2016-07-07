<?php namespace App;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;

class PatientInfo extends Model {

	 const CALL_WINDOW_0930_1200 = '9:30am - 12n';
	 const CALL_WINDOW_1200_1500 = '12n - 3pm';
	 const CALL_WINDOW_1500_1800 = '3pm - 6pm';

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
	protected $table = 'patient_info';

	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primaryKey = 'id';

	protected $guarded = [];

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




	/* @todo: put following helper functions it's own service class */

	public function getPatientPreferredTimes($patient){

		$time = Carbon::parse($patient->patientInfo->preferred_contact_time)->format('H:i');

		$days = PatientInfo::numberToTextDaySwitcher($patient->patientInfo->preferred_cc_contact_days);
		$days = $days = explode(',', $days);
		$days_formatted = array();


		foreach ($days as $day){
			$days_formatted[] = Carbon::parse($day)->format('Y-m-d');
		}

		return [

			'days' => $days_formatted,
			'time' => $time

		];
	}

	public function parsePatientCallPreferredWindow($patient){

		$window_date_time_930am = Carbon::parse('09:30')->format('H:i');
		$window_date_time_12n = Carbon::parse('12:00')->format('H:i');
		$window_date_time_3pm = Carbon::parse('15:00')->format('H:i');
		$window_date_time_6pm = Carbon::parse('18:00')->format('H:i');

		$time = $patient->patientInfo->preferred_contact_time;

		switch ($time){
			case ($time >= $window_date_time_930am && $time < $window_date_time_12n):
				$window = PatientInfo::CALL_WINDOW_0930_1200; break;
			case ($time >= $window_date_time_12n && $time < $window_date_time_3pm):
				$window = PatientInfo::CALL_WINDOW_1200_1500; break;
			case ($time >= $window_date_time_3pm && $time > $window_date_time_6pm):
				$window = PatientInfo::CALL_WINDOW_1500_1800; break;
			default:
				$window = 'Not able to calculate suitable window'; break;
		}

		return $window;

	}

	public static function numberToTextDaySwitcher($string){

		$mapper = function($i){

			switch($i){
				case 1: return ' Mon'; break;
				case 2: return ' Tue'; break;
				case 3: return ' Wed'; break;
				case 4: return ' Thu'; break;
				case 5: return ' Fri'; break;
				case 6: return ' Sat'; break;
				case 7: return ' Sun'; break;
			}

			return '';

		};

		$days = explode(',', $string);

		$formatted = array_map($mapper, $days);
		
		return implode(',', $formatted);

	}


}

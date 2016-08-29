<?php namespace App;

use Carbon\Carbon;
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

	public function patientContactWindows()
	{
		return $this->hasMany('App\PatientContactWindow', 'patient_info_id', 'id');
	}

	public function patientSummaries()
	{
		return $this->hasMany('App\PatientMonthlySummary', 'patient_info_id', 'id');
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


	// daily_contact_window_start
	public function getDailyContactWindowStartAttribute() {
		if(empty($this->attributes['daily_contact_window_start'])) {
			return '';
		}
		return Carbon::parse($this->attributes['daily_contact_window_start'])->format('H:i');
	}
	public function setDailyContactWindowStartAttribute($value) {
		$this->attributes['daily_contact_window_start'] = $value;
		$this->save();
	}

	// daily_contact_window_end
	public function getDailyContactWindowEndAttribute() {
		if(empty($this->attributes['daily_contact_window_end'])) {
			return '';
		}
		return Carbon::parse($this->attributes['daily_contact_window_end'])->format('H:i');
	}
	public function setDailyContactWindowEndAttribute($value) {
		$this->attributes['daily_contact_window_end'] = $value;
		$this->save();
	}



	/* @todo: put following helper functions it's own service class */

	public function getPatientPreferredTimes($patient){

		$window_start = Carbon::parse($patient->patientInfo->daily_contact_window_start)->format('H:i');
		$window_end = Carbon::parse($patient->patientInfo->daily_contact_window_end)->format('H:i');

		$days = PatientInfo::numberToTextDaySwitcher($patient->patientInfo->preferred_cc_contact_days);
		$days = $days = explode(',', $days);
		$days_formatted = array();


		foreach ($days as $day){
			$days_formatted[] = Carbon::parse($day)->format('Y-m-d');
		}

		return [

			'days' => $days_formatted,
			'window_start' => $window_start,
			'window_end' => $window_end

		];
	}

	public function parsePatientCallPreferredWindow($patient){

		$window_start = Carbon::parse($patient->patientInfo->daily_contact_window_start)->format('H:i');
		$window_end = Carbon::parse($patient->patientInfo->daily_contact_window_end)->format('H:i');

		return [

			'start' => $window_start,
			'end' => $window_end
		];

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


	// Return s current months CCM time formatted for UI
	public function getCurrentMonthCCMTimeAttribute()
	{
		$seconds = $this->cur_month_activity_time;
		$H = floor($seconds / 3600);
		$i = ($seconds / 60) % 60;
		$s = $seconds % 60;
		$monthlyTime = sprintf("%02d:%02d:%02d",$H, $i, $s);

		return $monthlyTime;
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

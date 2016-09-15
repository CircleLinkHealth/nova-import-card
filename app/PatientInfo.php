<?php namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'ID');
	}

	public function patientContactWindows()
	{
		return $this->hasMany(PatientContactWindow::class, 'patient_info_id', 'id');
	}

	public function patientSummaries()
	{
		return $this->hasMany(PatientMonthlySummary::class, 'patient_info_id', 'id');
	}

    public function carePlanProviderApproverUser()
    {
        return $this->belongsTo(User::class, 'user_id', 'ID');
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

	//Query Scopes:

	public function scopeEnrolled($query){

		return $query->where('ccm_status', 'enrolled');

	}

    /**
     * Import Patient's Call Window from the sheet, or save default.
     *
     * @param array $days | eg. [1,2,3] Monday is 1
     * @param $fromTime | eg. '09:00:00'
     * @param $toTime | eg. '17:00:00'
     * @return array of PatientContactWindows
     */
    public function attachNewOrDefaultCallWindows(array $days, $fromTime, $toTime)
    {
        $daysNumber = [1, 2, 3, 4, 5];

        if (!empty($days)) $daysNumber = $days;

        $timeFrom = '09:00:00';
        $timeTo = '17:00:00';

        if (!empty($fromTime)) $timeFrom = Carbon::parse($fromTime)->format('H:i:s');
        if (!empty($toTime)) $timeTo = Carbon::parse($toTime)->format('H:i:s');

        return PatientContactWindow::sync(
            $this,
            $daysNumber,
            $timeFrom,
            $timeTo
        );
    }

}

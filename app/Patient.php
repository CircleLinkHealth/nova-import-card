<?php namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model {

	use SoftDeletes;
	use \Venturecraft\Revisionable\RevisionableTrait;

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

    public static function boot()
    {
        parent::boot();
    }

	// START RELATIONSHIPS

    public static function numberToTextDaySwitcher($string)
    {

        $mapper = function ($i) {

            switch ($i) {
                case 1:
                    return ' Mon';
                    break;
                case 2:
                    return ' Tue';
                    break;
                case 3:
                    return ' Wed';
                    break;
                case 4:
                    return ' Thu';
                    break;
                case 5:
                    return ' Fri';
                    break;
                case 6:
                    return ' Sat';
                    break;
                case 7:
                    return ' Sun';
                    break;
            }

            return '';

        };

        $days = explode(',', $string);

        $formatted = array_map($mapper, $days);

        return implode(',', $formatted);

    }

	public function user()
	{
        return $this->belongsTo(User::class, 'user_id', 'id');
	}

	public function patientContactWindows()
	{
		return $this->hasMany(PatientContactWindow::class, 'patient_info_id', 'id');
	}

	public function patientSummaries()
	{
		return $this->hasMany(PatientMonthlySummary::class, 'patient_info_id', 'id');
	}

	// END RELATIONSHIPS


	// START ATTRIBUTES

	// first_name

    public function family()
    {

        return $this->belongsTo(Family::class, 'family_id');

    }

	public function getFirstNameAttribute() {
		return $this->user->first_name;
	}

    // last_name

	public function setFirstNameAttribute($value) {
		$this->user->first_name = $value;
		$this->user->save();
		return true;
	}

	public function getLastNameAttribute() {
		return $this->user->last_name;
	}

    // address

	public function setLastNameAttribute($value) {
		$this->user->last_name = $value;
		$this->user->save();
		return true;
	}

	public function getAddressAttribute() {
		return $this->user->address;
	}

    // city

	public function setAddressAttribute($value) {
		$this->user->address = $value;
		$this->user->save();
		return true;
	}

	public function getCityAttribute() {
		return $this->user->city;
	}

    // state

	public function setCityAttribute($value) {
		$this->user->city = $value;
		$this->user->save();
		return true;
	}

	public function getStateAttribute() {
		return $this->user->state;
	}

    // zip

	public function setStateAttribute($value) {
		$this->user->state = $value;
		$this->user->save();
		return true;
	}

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

	public function getFamilyMembers(Patient $patient){

		$family = $patient->family;

		if(is_object($family)){

			$members = $family->patients()->get();

            //remove the patient from the family itself
			return $members->reject(function ($item) {
				return $item->id == $this->id;
			});
		}

		return [];

	}

	public function getCurrentMonthCCMTimeAttribute()
	{
		$seconds = $this->cur_month_activity_time;
		$H = floor($seconds / 3600);
		$i = ($seconds / 60) % 60;
		$s = $seconds % 60;
		$monthlyTime = sprintf("%02d:%02d:%02d",$H, $i, $s);

		return $monthlyTime;
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

	public function scopeHasFamily($query){

		return $query->whereNotNull('family_id');

	}
	
	public function lastReachedNurse(){

		return Call::where('inbound_cpm_id', $this->user_id)
				   ->whereNotNull('called_date')
				   ->orderBy('called_date', 'desc')
				   ->first()['outbound_cpm_id'];

	}

	public function lastNurseThatPerformedActivity(){

		$id =  Activity::where('patient_id', $this->user_id)
				   ->whereHas('provider', function ($q){
					   $q->whereHas('roles', function ($k){
						   $k->where('name', 'care-center');
					   });
				   })
				   ->orderBy('created_at', 'desc')
				   ->first()['provider_id'];
		
		return Nurse::where('user_id', $id)->first();

	}


	/**
	 * Returns nurseInfos that have:
     *  - a call window in the future
     *  - location intersection with the patient's preferred contact location
     *
	 */

    public function nursesThatCanCareforPatient(){

        //Get user's programs

        $nurses = Nurse
            ::whereHas('windows', function($q){
                $q->where('date', '>', Carbon::now()->toDateTimeString());
            })->get();

        //Result array with Nurses
        $result = [];

        foreach ($nurses as $nurse){

            if($nurse->user->user_status == 1) {

                //get all locations for nurse
                $nurse_programs = $nurse->user->viewableProgramIds();

                $intersection = in_array($this->user->program_id, $nurse_programs);

                //to optimize further, check whether the nurse has any windows upcoming
                $future_windows = $nurse->windows->where('date', '>', Carbon::now()->toDateTimeString());

                //check if they can care for patient AND if they have a window.
                if ($intersection && $future_windows->count() > 0) {
                    $result[] = $nurse;
                }
            }

        }

        return $result;

    }

    public function isCCMComplex()
    {

        return $this->patientSummaries
            ->where('month_year', Carbon::now()
                ->firstOfMonth()
                ->toDateString())->first()->is_ccm_complex ?? false;

    }


}

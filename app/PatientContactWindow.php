<?php namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Auth;
use phpDocumentor\Reflection\Types\Object_;

class PatientContactWindow extends Model {

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

	protected $guarded = ['id'];

	// START RELATIONSHIPS

	public function patient_info()
	{
		return $this->belongsTo(PatientInfo::class);
	}

	// END RELATIONSHIPS

	public function getEarliestWindowForPatient($patient){

		$patient_windows = $patient->patientInfo->patientContactWindows()->get();

		//If there are no contact windows, we just return the next day for now. @todo confirm logic
		if(!$patient_windows){

			return Carbon::tomorrow()->toDateTimeString();

		}

		// leaving first blank to offset weird way of storing week as 1-7 instead of 0-6.
		// Returns a datetime string with all the necessary time information
		$week = ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

		$min_date = Carbon::maxValue();

		foreach ($patient_windows as $window){

			$carbon_date = Carbon::parse('next ' . $week[$window->day_of_week]);

			$carbon_hour = Carbon::parse($window->window_time_start)->format('H');
			$carbon_minutes = Carbon::parse($window->window_time_start)->format('i');
			$carbon_date->setTime($carbon_hour, $carbon_minutes);

			$date_string = $carbon_date->toDateTimeString();

			if($min_date > $date_string){
				$min_date = $date_string;
				$min_date_carbon = $date_string;
				$closest_window = $window;
			}
		}

		return [

			'day' => $min_date_carbon,
			'window_start' => Carbon::parse($closest_window->window_time_start)->format('H:i'),
			'window_end' => Carbon::parse($closest_window->window_time_end)->format('H:i')

		];

	}


}

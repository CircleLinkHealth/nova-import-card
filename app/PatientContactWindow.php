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

	public function getEarliestWindowForPatient(User $patient){

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

	public function getEarliestWindowForPatientFromDate(User $patient, Carbon $date){

		$patient_windows = $patient->patientInfo->patientContactWindows()->get();

		if(!$patient_windows){

			return $date->tomorrow()->toDateTimeString();

		}

		// leaving first blank to offset weird way of storing week as 1-7 instead of 0-6.
		// Returns a datetime string with all the necessary time information
		$week = ['', Carbon::MONDAY, Carbon::TUESDAY, Carbon::WEDNESDAY, Carbon::THURSDAY, Carbon::FRIDAY, Carbon::SATURDAY, Carbon::SUNDAY];

		$min_date = Carbon::maxValue();

		foreach ($patient_windows as $window){

			$carbon_date = $date->next($week[$window->day_of_week]);

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


	//Returns Array with each element containing a start_window_time and an end_window_time in dateString format
	public static function getNextWindowsForPatient($patient){

		$patient_windows = $patient->patientInfo->patientContactWindows()->get();

		//If there are no contact windows, we just return the next day for now. @todo confirm logic
		if(!$patient_windows){

			return Carbon::tomorrow()->toDateTimeString();

		}

		// leaving first blank to offset weird way of storing week as 1-7 instead of 0-6.
		// Returns a datetime string with all the necessary time information
		$week = ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

		$windows = array();
		$count = 0;

		foreach ($patient_windows as $window){

			$carbon_date_start = Carbon::parse('next ' . $week[$window->day_of_week]);
			$carbon_date_end = Carbon::parse('next ' . $week[$window->day_of_week]);


			$carbon_hour_start = Carbon::parse($window->window_time_start)->format('H');
			$carbon_minutes_start = Carbon::parse($window->window_time_start)->format('i');

			$carbon_hour_end = Carbon::parse($window->window_time_end)->format('H');
			$carbon_minutes_end = Carbon::parse($window->window_time_end)->format('i');

			$carbon_date_start->setTime($carbon_hour_start, $carbon_minutes_start);
			$carbon_date_end->setTime($carbon_hour_end, $carbon_minutes_end);

			$windows[$count]['string_start'] = $carbon_date_start->toDateTimeString();
			$windows[$count]['string_end'] = $carbon_date_end->toDateTimeString();
			$count++;
		}


		//current solution to double the number of windows, add a week and give more options. @todo refactor

		foreach ($patient_windows as $window){

			$carbon_date_start = Carbon::parse('next ' . $week[$window->day_of_week])->addWeek(1);
			$carbon_date_end = Carbon::parse('next ' . $week[$window->day_of_week])->addWeek(1);


			$carbon_hour_start = Carbon::parse($window->window_time_start)->format('H');
			$carbon_minutes_start = Carbon::parse($window->window_time_start)->format('i');

			$carbon_hour_end = Carbon::parse($window->window_time_end)->format('H');
			$carbon_minutes_end = Carbon::parse($window->window_time_end)->format('i');

			$carbon_date_start->setTime($carbon_hour_start, $carbon_minutes_start);
			$carbon_date_end->setTime($carbon_hour_end, $carbon_minutes_end);

			$windows[$count]['string_start'] = $carbon_date_start->toDateTimeString();
			$windows[$count]['string_end'] = $carbon_date_end->toDateTimeString();
			$count++;
		}

		return collect($windows)->sort()->toArray();

	}


}

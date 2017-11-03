<?php namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PatientContactWindow extends \App\BaseModel
{

    protected $table = 'patient_contact_window';

    protected $primaryKey = 'id';

    protected $guarded = [];

    protected $attributes = [
        'window_time_start' => '09:00:00',
        'window_time_end'   => '17:00:00',
    ];

    // START RELATIONSHIPS

    public static function getNextWindowsForPatient($patient)
    {

        $patient_windows = $patient->patientInfo->patientContactWindows()->get();

        //If there are no contact windows, we just return the next day for now. @todo confirm logic
        if (!$patient_windows) {
            return Carbon::tomorrow()->toDateTimeString();
        }

        // leaving first blank to offset weird way of storing week as 1-7 instead of 0-6.
        // Returns a datetime string with all the necessary time information
        $week = [
            '',
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday',
        ];
        $windows = [];
        $count = 0;

        foreach ($patient_windows as $window) {
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

        foreach ($patient_windows as $window) {
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

    // END RELATIONSHIPS

    public static function getNextWindowsForPatientFromDate(
        $patient,
        $offset_date
    ) {

        $patient_windows = $patient->patientContactWindows->all();

        //to count the current day in the calculation as well, we sub one day.
        $offset_date = Carbon::parse($offset_date)->subDay()->toDateString();

        $windows = [];

        //If there are no contact windows, we just the same day. @todo confirm logic
        if (!$patient_windows) {
            $carbon_date_start = Carbon::parse($offset_date);
            $carbon_date_end = Carbon::parse($offset_date);

            $carbon_date_start->setTime('10', '00');
            $carbon_date_end->setTime('12', '00');

            $windows[0]['window_start'] = $carbon_date_start->toDateTimeString();
            $windows[0]['window_end'] = $carbon_date_end->toDateTimeString();

            return collect($windows);
        }

        // leaving first blank to offset weird way of storing week as 1-7 instead of 0-6.
        // Returns a datetime string with all the necessary time information
        $week = [
            '',
            Carbon::MONDAY,
            Carbon::TUESDAY,
            Carbon::WEDNESDAY,
            Carbon::THURSDAY,
            Carbon::FRIDAY,
            Carbon::SATURDAY,
            Carbon::SUNDAY,
        ];
        $count = 0;

        //The date from the algorithm is supplied here to find the amount of time we wait before calling him back.

        //In this section, we loop through the next days of the week that the patient is available,
        //and after assigning the times to the carbon object, we add it to an array as available
        //contact days

        //Since we need a static date to keep adding to
        //$offset_date = $offset_date->toDateTimeString();

        $weeks_range = 2;

        for ($i = 0; $i < $weeks_range; $i++) {
            //add windows for each week needed.

            foreach ($patient_windows as $window) {
                //the first date should include the target date, so we backtrack one day
                //and see whether the date is a window.

                if ($i == 0) {
                    $carbon_date_start = Carbon::parse($offset_date)->subDay()->next($week[$window->day_of_week]);
                    $carbon_date_end = Carbon::parse($offset_date)->subDay()->next($week[$window->day_of_week]);
                } else {
                    $carbon_date_start = Carbon::parse($offset_date)->addWeek($i)->next($week[$window->day_of_week]);
                    $carbon_date_end = Carbon::parse($offset_date)->addWeek($i)->next($week[$window->day_of_week]);
                }

                $carbon_hour_start = Carbon::parse($window->window_time_start)->format('H');
                $carbon_minutes_start = Carbon::parse($window->window_time_start)->format('i');

                $carbon_hour_end = Carbon::parse($window->window_time_end)->format('H');
                $carbon_minutes_end = Carbon::parse($window->window_time_end)->format('i');

                $carbon_date_start->setTime($carbon_hour_start, $carbon_minutes_start);
                $carbon_date_end->setTime($carbon_hour_end, $carbon_minutes_end);

                $windows[$count]['window_start'] = $carbon_date_start->toDateTimeString();
                $windows[$count]['window_end'] = $carbon_date_end->toDateTimeString();
                $count++;
            }
        }

        return collect($windows)->sort();
    }

    //Returns Array with each element containing a start_window_time and an end_window_time in dateString format

    /**
     * Delete all current call windows. Then add the ones given.
     * Returns an array of contact windows created.
     *
     * @param Patient $info
     * @param array $days
     * @param string $windowStart
     * @param string $windowEnd
     *
     * @return array $created
     */
    public static function sync(
        Patient $info,
        array $days = [],
        $windowStart = '09:00:00',
        $windowEnd = '17:00:00'
    ) {
        $created = [];

        //first delete all call windows
        $info->patientContactWindows()->delete();

        foreach ($days as $dayId) {
            $created[] = PatientContactWindow::create([
                'patient_info_id'   => $info->id,
                'day_of_week'       => $dayId,
                'window_time_start' => $windowStart,
                'window_time_end'   => $windowEnd,
            ]);
        }

        return $created;
    }

    public static function getPreferred(Patient $patientInfo)
    {
        $window = PatientContactWindow::firstOrNew([
            'patient_info_id' => $patientInfo->id,
        ]);

        $window_start = Carbon::parse($window->window_time_start)->format('H:i');
        $window_end = Carbon::parse($window->window_time_end)->format('H:i');

        return [
            'start' => $window_start,
            'end'   => $window_end,
        ];
    }

    //Returns Array with each element containing a start_window_time and an end_window_time in dateString format

    public function patient_info()
    {
        return $this->belongsTo(Patient::class);
    }

    public function getEarliestWindowForPatient(User $patient)
    {

        $patient_windows = $patient->patientInfo->patientContactWindows()->get();

        //If there are no contact windows, we just return the next day for now. @todo confirm logic
        if (!$patient_windows) {
            return Carbon::tomorrow()->toDateTimeString();
        }

        // leaving first blank to offset weird way of storing week as 1-7 instead of 0-6.
        // Returns a datetime string with all the necessary time information
        $week = [
            '',
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday',
        ];

        $min_date = Carbon::maxValue();

        foreach ($patient_windows as $window) {
            $carbon_date = Carbon::parse('next ' . $week[$window->day_of_week]);

            $carbon_hour = Carbon::parse($window->window_time_start)->format('H');
            $carbon_minutes = Carbon::parse($window->window_time_start)->format('i');
            $carbon_date->setTime($carbon_hour, $carbon_minutes);

            $date_string = $carbon_date->toDateTimeString();

            if ($min_date > $date_string) {
                $min_date = $date_string;
                $min_date_carbon = $date_string;
                $closest_window = $window;
            }
        }

        return [

            'day'          => $min_date_carbon,
            'window_start' => Carbon::parse($closest_window->window_time_start)->format('H:i'),
            'window_end'   => Carbon::parse($closest_window->window_time_end)->format('H:i'),

        ];
    }

    public function getEarliestWindowForPatientFromDate(
        Patient $patient,
        Carbon $offset_date
    ) {


        $patient_windows = $patient->patientContactWindows()->get();

        //If no contact window, just return the same date.
        if ($patient_windows->count() == 0) {
            //to make sure the day returned is a weekday for calls.
            while (!$offset_date->isWeekday()) {
                $offset_date->addDay();
            }

            $day = $offset_date->toDateTimeString();

            return [

                'day'          => $day,
                'window_start' => Carbon::parse('09:00:00')->format('H:i'),
                'window_end'   => Carbon::parse('17:00:00')->format('H:i'),

            ];
        }

        $adjusted_offset = Carbon::parse($offset_date)->subDay()->toDateString();

        foreach ($patient_windows as $window) {
            $days[] = Carbon::parse($adjusted_offset)->next(clhToCarbonDayOfWeek($window->day_of_week));
        }

        $date = min($days)->toDateString();

        return [

            'day'          => $date,
            'window_start' => Carbon::parse($window->window_time_start)->format('H:i'),
            'window_end'   => Carbon::parse($window->window_time_end)->format('H:i'),

        ];

        // we sub one day to check whether the offset day is an option.
    }
}

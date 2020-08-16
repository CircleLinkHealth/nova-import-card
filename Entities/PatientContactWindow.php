<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Events\PatientContactWindowUpdatedEvent;

/**
 * CircleLinkHealth\Customer\Entities\PatientContactWindow.
 *
 * @property int                                         $id
 * @property int                                         $patient_info_id
 * @property int                                         $day_of_week
 * @property string                                      $window_time_start
 * @property string                                      $window_time_end
 * @property \Carbon\Carbon|null                         $created_at
 * @property \Carbon\Carbon|null                         $updated_at
 * @property \CircleLinkHealth\Customer\Entities\Patient $patient_info
 * @method   static                                      \Illuminate\Database\Eloquent\Builder|\App\PatientContactWindow whereCreatedAt($value)
 * @method   static                                      \Illuminate\Database\Eloquent\Builder|\App\PatientContactWindow whereDayOfWeek($value)
 * @method   static                                      \Illuminate\Database\Eloquent\Builder|\App\PatientContactWindow whereId($value)
 * @method   static                                      \Illuminate\Database\Eloquent\Builder|\App\PatientContactWindow wherePatientInfoId($value)
 * @method   static                                      \Illuminate\Database\Eloquent\Builder|\App\PatientContactWindow whereUpdatedAt($value)
 * @method   static                                      \Illuminate\Database\Eloquent\Builder|\App\PatientContactWindow whereWindowTimeEnd($value)
 * @method   static                                      \Illuminate\Database\Eloquent\Builder|\App\PatientContactWindow whereWindowTimeStart($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientContactWindow newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientContactWindow newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientContactWindow query()
 * @property int|null                                                                                    $revision_history_count
 */
class PatientContactWindow extends BaseModel
{
    protected $attributes = [
        'window_time_start' => '09:00:00',
        'window_time_end'   => '17:00:00',
    ];

    protected $guarded = [];

    protected $primaryKey = 'id';
    protected $table      = 'patient_contact_window';

    public function getEarliestWindowForPatientFromDate(
        Patient $patient,
        Carbon $offset_date
    ) {
        $offset_date = $offset_date->copy();

        $patient_windows = $patient->contactWindows;

        if (0 == $patient_windows->count()) {
            do {
                $offset_date->addDay();
            } while ( ! $offset_date->isWeekday());

            $day = $offset_date->toDateTimeString();

            return [
                'day'          => $day,
                'window_start' => Carbon::parse('09:00:00')->format('H:i'),
                'window_end'   => Carbon::parse('17:00:00')->format('H:i'),
            ];
        }

        $adjusted_offset = $offset_date->copy()->subDay();

        foreach ($patient_windows as $window) {
            $dateOption = $adjusted_offset->copy()->next(clhToCarbonDayOfWeek($window->day_of_week));

            if ($dateOption->lt(now())) {
                $dateOption = $dateOption->copy()->next(clhToCarbonDayOfWeek($window->day_of_week));
            }

            $days[] = $dateOption;
        }

        $date = min($days)->toDateString();

        return [
            'day'          => $date,
            'window_start' => Carbon::parse($window->window_time_start)->format('H:i'),
            'window_end'   => Carbon::parse($window->window_time_end)->format('H:i'),
        ];
    }

    public static function getPreferred(Patient $patientInfo)
    {
        if ($patientInfo->relationLoaded('contactWindows') && $patientInfo->contactWindows->isNotEmpty()) {
            $window = $patientInfo->contactWindows->first();
        } else {
            $window = PatientContactWindow::firstOrNew([
                'patient_info_id' => $patientInfo->id,
            ]);
        }

        $window_start = Carbon::parse($window->window_time_start)->format('H:i');
        $window_end   = Carbon::parse($window->window_time_end)->format('H:i');

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

    //Returns Array with each element containing a start_window_time and an end_window_time in dateString format

    /**
     * Delete all current call windows. Then add the ones given.
     * Returns an array of contact windows created.
     *
     * @param string $windowStart
     * @param string $windowEnd
     *
     * @return array $created
     */
    public static function sync(
        Patient $info,
        ?array $days = [],
        $windowStart = '09:00:00',
        $windowEnd = '17:00:00'
    ) {
        $created = [];

        if ( ! $days) {
            $days = [];
        }

        //first delete all call windows
        $info->contactWindows()->delete();

        foreach ($days as $dayId) {
            $created[] = PatientContactWindow::create([
                'patient_info_id'   => $info->id,
                'day_of_week'       => $dayId,
                'window_time_start' => Carbon::parse($windowStart)->format('H:i'),
                'window_time_end'   => Carbon::parse($windowEnd)->format('H:i'),
            ]);
        }

        event(new PatientContactWindowUpdatedEvent($created));

        return $created;
    }
}

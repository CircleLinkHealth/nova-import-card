<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\NurseContactWindow;
use Validator;

trait ValidatesWorkScheduleCalendar
{
    public function invalidWorkHoursValidator($workHoursRangeSum, $committedWorkHours)
    {
        return $committedWorkHours > $workHoursRangeSum ? true : false;
    }

    public function returnValidationResponse($windowExists, $validator, $invalidWorkHoursCommitted, $workScheduleData, $holidayExists)
    {
        if ($holidayExists) {
            $validator->getMessageBag()->add(
                'error',
                'This day is assigned as day-off'
            );
        }

//        if ($workScheduleData['repeat_freq'] !=='does_not_repeat'
//            && $this->checkIfIsNotWeekend($workScheduleData['date'])) {
//            $validator->getMessageBag()->add(
//                'error',
//                'You cant start a repeated event in weekend'
//            );
//        }

        if ($invalidWorkHoursCommitted) {
            $validator->getMessageBag()->add(
                'error',
                'Daily work hours cannot be more than total window hours.'
            );
        }
        if ('does_not_repeat' !== $workScheduleData['repeat_freq'] && null === $workScheduleData['until']) {
            $validator->getMessageBag()->add(
                'error',
                'Please choose a repeat until date'
            );
        }
        if ($windowExists) {
            $validator->getMessageBag()->add(
                'error',
                'This window is overlapping with an already existing window.'
            );
        }


        return $validator;
    }

    public function validatorScheduleData($workScheduleData)
    {
        return Validator::make($workScheduleData, [
            'day_of_week' => 'required',
            'window_time_start' => 'required|date_format:H:i',
            'window_time_end' => 'required|date_format:H:i',
            'work_hours' => 'required|numeric',
            'date' => 'required|date',
        ]);
    }

    public function windowsExistsValidator($workScheduleData, $updateCollisions = false)
    {
        $nurseInfoId = $workScheduleData['nurse_info_id'];
        $windowTimeStart = $workScheduleData['window_time_start'];
        $windowTimeEnd = $workScheduleData['window_time_end'];
        $windowDate = $workScheduleData['date'];

        $windowExists = NurseContactWindow::where([
            [
                'nurse_info_id',
                '=',
                $nurseInfoId,
            ],
            [
                'window_time_end',
                '>=',
                $windowTimeStart,
            ],
            [
                'window_time_start',
                '<=',
                $windowTimeEnd,
            ],
            [
                'date',
                '=',
                $windowDate,
            ],
        ])->first();

        return !$updateCollisions ? $windowExists : false;
    }

    /**
     * @param $date
     * @return bool
     */
    public function checkIfIsNotWeekend($date)
    {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;

        return $dayOfWeek !== self::SATURDAY
            && $dayOfWeek !== self::SUNDAY;
    }

}

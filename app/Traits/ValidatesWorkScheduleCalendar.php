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
    /**
     * @param $date
     *
     * @return bool
     */
    public function checkIfIsNotWeekend($date)
    {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;

        return self::SATURDAY !== $dayOfWeek
            && self::SUNDAY !== $dayOfWeek;
    }

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
                'This day already has a scheduled event. 
                If you wish to change your schedule, please remove the existing event first.'
            );
        }

        return $validator;
    }

    public function validatorScheduleData($workScheduleData)
    {
        return Validator::make($workScheduleData, [
            'day_of_week'       => 'required',
            'window_time_start' => 'required|date_format:H:i',
            'window_time_end'   => 'required|date_format:H:i',
            'work_hours'        => 'required|numeric',
            'date'              => 'required|date',
        ]);
    }

    public function windowsExistsValidator($workScheduleData, $updateCollisions = false)
    {
        $nurseInfoId     = $workScheduleData['nurse_info_id'];
        $windowTimeStart = $workScheduleData['window_time_start'];
        $windowTimeEnd   = $workScheduleData['window_time_end'];
        $windowDate      = $workScheduleData['date'];

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

        return ! $updateCollisions ? $windowExists : false;
    }
}

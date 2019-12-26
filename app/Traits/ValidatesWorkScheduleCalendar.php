<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

use CircleLinkHealth\Customer\Entities\NurseContactWindow;
use Validator;

trait ValidatesWorkScheduleCalendar
{
    public function invalidWorkHoursValidator($workHoursRangeSum, $committedWorkHours)
    {
        return $committedWorkHours > $workHoursRangeSum ? true : false;
    }

    public function returnValidationResponse($windowExists, $validator, $invalidWorkHoursCommitted)
    {
        if ($windowExists) {
            $validator->getMessageBag()->add(
                'window_time_start',
                'This window is overlapping with an already existing window.'
            );
        }

        if ($invalidWorkHoursCommitted) {
            $validator->getMessageBag()->add(
                'work_hours',
                'Daily work hours cannot be more than total window hours.'
            );
        }

        return $validator;
    }

    public function validatorScheduleData($workScheduleData)
    {
        return Validator::make($workScheduleData, [
            'day_of_week'       => 'required',
            'window_time_start' => 'required|date_format:H:i',
            'window_time_end'   => 'required|date_format:H:i|after:window_time_start',
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

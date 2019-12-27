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

    public function returnValidationResponse($windowExists, $validator, $invalidWorkHoursCommitted, $repeatFr, $until)
    {
        if ('does_not_repeat' !== $repeatFr && null === $until) {
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

        if ($invalidWorkHoursCommitted) {
            $validator->getMessageBag()->add(
                'error',
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

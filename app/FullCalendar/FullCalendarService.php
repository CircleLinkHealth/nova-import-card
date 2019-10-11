<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\FullCalendar;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\WorkHours;
use Illuminate\Database\Eloquent\Collection;

class FullCalendarService
{
    const ALL_DAY = 'allDay';
    const END     = 'end';
    const LABEL   = 'label';
    const START   = 'start';
    const TITLE   = 'title';

    /**
     * @param Collection $nurses
     *
     * @return Collection|\Illuminate\Support\Collection
     *
     * note for antoni: Im sending dropdown data in a different collection cause i need all active nurses (no just the working RNs)
     */
    public function getDataForDropdown(Collection $nurses)
    {
        return $nurses->map(function ($nurse) {
            return [
                'nurseId' => $nurse->nurseInfo->id,
                'label'   => $nurse->display_name,
            ];
        });
    }

    /**
     * @param Collection $nurses
     *
     * @return Collection|\Illuminate\Support\Collection
     */
    public function getHolidays(Collection $nurses)
    {
        $limitDate = Carbon::parse(now())->startOfYear()->subMonth(2)->toDate();

        return $nurses->map(function ($nurse) use ($limitDate) {
            $holidays = $nurse->nurseInfo->holidays->where('date', '>=', $limitDate);

            return collect($holidays)->map(function ($holiday) use ($nurse) {
                $holidayDate = Carbon::parse($holiday->date)->toDateString();
                $holidayDateInDayOfWeek = Carbon::parse($holidayDate)->dayOfWeek;
                $holidayInHumanLang = clhDayOfWeekToDayName($holidayDateInDayOfWeek);

                return collect(
                    [
                        self::TITLE => "$nurse->display_name",
                        self::START => $holidayDate,
                        'allDay'    => true,
                        'color'     => '#ff5b4f',
                        'data'      => [
                            'holidayId' => $holiday->id,
                            'nurseId'   => $nurse->nurseInfo->id,
                            'name'      => $nurse->display_name,
                            'date'      => $holidayDate,
                            'day'       => $holidayInHumanLang,
                            'eventType' => 'holiday',
                        ],
                    ]
                );
            });
        })->flatten(1);
    }

    /**
     * @param $nurse
     * @param mixed $startOfThisYear
     *
     * @return \Illuminate\Support\Collection
     */
    public function prepareData($nurse, $startOfThisYear)
    {// I need to get all the past data cause events are created once and then are repeating.
        return collect($nurse->nurseInfo->windows)
            ->map(function ($window) use ($nurse) {
                $givenDateWeekMap = convertDayOfWeekToWeekDate($window->date); //see comment in helpers.php
                $dayInHumanLang = clhDayOfWeekToDayName($window->day_of_week);
                $workHoursForDay = WorkHours::where('workhourable_id', $nurse->nurseInfo->id)->pluck($dayInHumanLang)->first();

                return collect(
                    [
                        self::TITLE => "$workHoursForDay Hrs - $nurse->display_name 
                        {$window->window_time_start}-{$window->window_time_end}",
                        // self::START => "{$currentWeekMap[$window->day_of_week]}T{$window->window_time_start}",
                        self::START => "{$givenDateWeekMap[$window->day_of_week]}", //no time = repeated event
                        self::END   => "{$givenDateWeekMap[$window->day_of_week]}T{$window->window_time_end}",
                        //@todo: add until - date to repeat event
                        'color'     => '#5bc0ded6',
                        'textColor' => '#fff',

                        'data' => [
                            'nurseId'   => $nurse->nurseInfo->id,
                            'windowId'  => $window->id,
                            'name'      => $nurse->display_name,
                            'day'       => $dayInHumanLang,
                            'date'      => $givenDateWeekMap[$window->day_of_week],
                            'start'     => $window->window_time_start,
                            'end'       => $window->window_time_end,
                            'workHours' => $workHoursForDay,
                            'eventType' => 'workDay',
                        ],
                    ]
                );
            });
    }

    /**
     * @param Collection $nurses
     * @param mixed      $startOfThisYear
     * @param $startOfThisWeek
     * @param $endOfThisWeek
     *
     * @return Collection|\Illuminate\Support\Collection
     */
    public function prepareDataForCalendar(Collection $nurses, $startOfThisYear)
    {
        return $nurses->map(function ($nurse) use ($startOfThisYear) {
            return $this->prepareData($nurse, $startOfThisYear);
        })->flatten(1);
    }
}

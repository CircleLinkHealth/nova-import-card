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
    public function getUpcomingHolidays(Collection $nurses)
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

                        'allDay' => true,
                        'color'  => '#ff5b4f',
                        'data'   => [
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
     * @param $startOfThisWeek
     * @param $endOfThisWeek
     *
     * @return \Illuminate\Support\Collection
     */
    public function prepareData($nurse, $startOfThisYear, $startOfThisWeek, $endOfThisWeek)
    {// I need to get the past data cause we dont know when they were edited last time. (events are created once and then are repeating)
        return collect($nurse->nurseInfo->windows)
            ->where('date', '>=', $startOfThisYear)
            ->map(function ($window) use ($nurse, $startOfThisWeek, $endOfThisWeek) {
                $weekMap = dayOfWeekToDate($window->date);
                $dayInHumanLang = clhDayOfWeekToDayName($window->day_of_week);
                $workHoursForDay = WorkHours::where('workhourable_id', $nurse->nurseInfo->id)->pluck($dayInHumanLang)->first();

                return collect(
                    [
                        self::TITLE => "$nurse->display_name - $workHoursForDay Hrs",
                        //                                            self::START => "{$weekMap[$window->day_of_week]}T{$window->window_time_start}",
                        //                                            self::END   => "{$weekMap[$window->day_of_week]}T{$window->window_time_end}",

                        self::START => $weekMap[$window->day_of_week],
                        self::END   => $window->window_time_end,

                        //                        'allDay' => true,
                        'color' => '#378006',
                        'dow'   => [$window->day_of_week], //@todo:need to fix plugin(rrule)for this to work

                        'data' => [
                            'nurseId'   => $nurse->nurseInfo->id,
                            'windowId'  => $window->id,
                            'name'      => $nurse->display_name,
                            'day'       => $dayInHumanLang,
                            'date'      => $weekMap[$window->day_of_week],
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
    public function prepareDatesForCalendar(Collection $nurses, $startOfThisYear, $startOfThisWeek, $endOfThisWeek)
    {
        return $nurses->map(function ($nurse) use ($startOfThisYear, $startOfThisWeek, $endOfThisWeek) {
            return $this->prepareData($nurse, $startOfThisYear, $startOfThisWeek, $endOfThisWeek);
        })->flatten(1);
    }
}

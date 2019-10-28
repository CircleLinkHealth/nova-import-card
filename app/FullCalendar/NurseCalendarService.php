<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\FullCalendar;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\NurseContactWindow;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Entities\WorkHours;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class NurseCalendarService
{
    const ALL_DAY = 'allDay';
    const END     = 'end';
    const LABEL   = 'label';
    const START   = 'start';
    const TITLE   = 'title';

    /**
     * @param $nurseInfoId
     * @param $windowTimeStart
     * @param $windowTimeEnd
     * @param $windowDayOfWeek
     * @param mixed $windowDate
     *
     * @return Builder|Model|object|null
     */
    public function checkIfWindowsExists($nurseInfoId, $windowTimeStart, $windowTimeEnd, $windowDate)
    {
        return NurseContactWindow::where([
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
            //            [
            //                'day_of_week',
            //                '=',
            //                $windowDayOfWeek,
            //            ],
        ])->first();
    }

    /**
     * @param $diffRange
     * @param $eventDate
     * @param $repeatFrequency
     *
     * @return array|\Illuminate\Support\Collection
     */
    public function createRecurringDates($diffRange, $eventDate, $repeatFrequency)
    {
        $defaultRecurringDates = collect();

        if ('weekly' === $repeatFrequency) {
            for ($i = 0; $i < $diffRange; ++$i) {
                $defaultRecurringDates[] = Carbon::parse($eventDate)->copy()->addWeek($i)->toDateString();
            }
        }

        if ('monthly' === $repeatFrequency) {
            for ($i = 0; $i < $diffRange; ++$i) {
                $defaultRecurringDates[] = Carbon::parse($eventDate)->copy()->addMonth($i)->toDateString();
            }
        }

        if ('daily' === $repeatFrequency) {
            for ($i = 0; $i < $diffRange; ++$i) { //@todo:should exclude weekedns option
                $defaultRecurringDates[] = Carbon::parse($eventDate)->copy()->addDay($i)->toDateString();
            }
        }

        return $defaultRecurringDates;
    }

    /**
     * @param $eventDate
     * @param $nurseInfoId
     * @param $windowTimeStart
     * @param $windowTimeEnd
     * @param null $frequency
     * @param null $repeatUntil
     *
     * @return \Illuminate\Support\Collection
     */
    public function createRecurringEvents($eventDate, $nurseInfoId, $windowTimeStart, $windowTimeEnd, $frequency = null, $repeatUntil = null)
    {
        //converts the date that event was saved to - date that event is scheduled for + transfered to current's week (dow)
        //So events are starting to repeat from release's date week. This is what we want?
        $repeatFrequency   = null === $frequency ? 'weekly' : $frequency;
        $defaultRepeatDate = Carbon::parse($eventDate)->copy()->addMonths(4)->toDateString();
        $repeatEventUntil  = null === $repeatUntil ? $defaultRepeatDate : $repeatUntil;
        $rangeToRepeat     = $this->getWeeksOrDaysToRepeat($eventDate, $repeatEventUntil, $repeatFrequency);
        $validatedDefault  = 'not_checked';

        $recurringDates = $this->createRecurringDates($rangeToRepeat, $eventDate, $repeatFrequency);

        return $this->createWindowsArrays($recurringDates, $nurseInfoId, $windowTimeStart, $windowTimeEnd, $eventDate, $validatedDefault, $repeatFrequency, $repeatEventUntil);
    }

    /**
     * @param $defaultRecurringDates
     * @param $window
     * @param $eventDate
     * @param $validatedDefault
     * @param $defaultRepeatFreq
     * @param $repeatEventByDefaultUntil
     * @param mixed $nurseInfoId
     * @param mixed $windowTimeStart
     * @param mixed $windowTimeEnd
     *
     * @return \Illuminate\Support\Collection
     */
    public function createWindowsArrays($defaultRecurringDates, $nurseInfoId, $windowTimeStart, $windowTimeEnd, $eventDate, $validatedDefault, $defaultRepeatFreq, $repeatEventByDefaultUntil)
    {
        return collect($defaultRecurringDates)
            ->map(function ($date) use ($defaultRecurringDates, $nurseInfoId, $windowTimeStart, $windowTimeEnd, $eventDate, $validatedDefault, $defaultRepeatFreq, $repeatEventByDefaultUntil) {
                return [
                    'nurse_info_id'     => $nurseInfoId,
                    'date'              => $date,
                    'day_of_week'       => Carbon::parse($eventDate)->dayOfWeek,
                    'window_time_start' => $windowTimeStart,
                    'window_time_end'   => $windowTimeEnd,
                    'validated'         => $validatedDefault,
                    'repeat_frequency'  => $defaultRepeatFreq,
                    'until'             => $repeatEventByDefaultUntil,
                    'created_at'        => Carbon::parse(now())->toDateTimeString(),
                    'updated_at'        => Carbon::parse(now())->toDateTimeString(),
                ];
            });
    }

    /**
     * @param $events
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCollidingDates($events)
    {
        return collect($events)->map(function ($event) {
            return Carbon::parse($event['date'])->toDateString();
        });
    }

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
     * @param $recurringEventsToSave
     *
     * @return array
     */
    public function getEventsToAskConfirmation($recurringEventsToSave)
    {
        $askForConfirmationEvents = [];
        foreach ($recurringEventsToSave as $event) {
            $windowsExists = $this->checkIfWindowsExists(
                $event['nurse_info_id'],
                $event['window_time_start'],
                $event['window_time_end'],
                $event['date']
            );
            if ($windowsExists) {
                $askForConfirmationEvents[] = $windowsExists;
            }
        }

        return $askForConfirmationEvents;
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

    public function getNursesWithSchedule()
    {
        $workScheduleData = [];
        User::ofType('care-center')
            ->with('nurseInfo.windows', 'nurseInfo.holidays')
            ->whereHas('nurseInfo', function ($q) {
                $q->where('status', 'active');
            })
            ->chunk(100, function ($nurses) use (&$workScheduleData) {
                $workScheduleData[] = $nurses;
            });

        return $workScheduleData[0];
    }

    /**
     * @param $eventDate
     * @param $repeatUntil
     * @param $repeatFrequency
     *
     * @return int
     */
    public function getWeeksOrDaysToRepeat($eventDate, $repeatUntil, $repeatFrequency)
    {
        return 'daily' !== $repeatFrequency
            ? Carbon::parse($eventDate)->diffInWeeks($repeatUntil)
            : Carbon::parse($eventDate)->diffInDays($repeatUntil);
    }

    /**
     * @param $nurse
     * @param mixed $startOfThisYear
     *
     * @return \Illuminate\Support\Collection
     */
    public function prepareData($nurse)
    {
        return collect($nurse->nurseInfo->windows)
            ->where('repeat_frequency', '!=', null)
            ->map(function ($window) use ($nurse) {
                $givenDateWeekMap = createWeekMap($window->date); //see comment in helpers.php
                $dayInHumanLang = clhDayOfWeekToDayName($window->day_of_week);
                $workHoursForDay = WorkHours::where('workhourable_id', $nurse->nurseInfo->id)->pluck($dayInHumanLang)->first();
                $windowStartForView = Carbon::parse($window->window_time_start)->format('H:i');
                $windowEndForView = Carbon::parse($window->window_time_end)->format('H:i');
                $windowDate = Carbon::parse($window->date)->toDateString();
                $hoursAbrev = 'h';
                $color = '#5bc0ded6';

                return collect(
                    [
                        self::TITLE => "$nurse->display_name ({$workHoursForDay}$hoursAbrev)
                        {$windowStartForView}-{$windowEndForView}",
                        self::START        => "{$windowDate}T{$window->window_time_start}",
                        self::END          => "{$windowDate}T{$window->window_time_end}",
                        'color'            => $color,
                        'textColor'        => '#fff',
                        'repeat_frequency' => $window->repeat_frequency,
                        'until'            => $window->until,
                        'allDay'           => true,
                        'data'             => [
                            'nurseId'      => $nurse->nurseInfo->id,
                            'windowId'     => $window->id,
                            'name'         => $nurse->display_name,
                            'day'          => $dayInHumanLang,
                            'date'         => $givenDateWeekMap[$window->day_of_week],
                            'start'        => $window->window_time_start,
                            'end'          => $window->window_time_end,
                            'workHours'    => $workHoursForDay,
                            'eventType'    => 'workDay',
                            'clhDayOfWeek' => $window->day_of_week,
                        ],
                    ]
                );
            });
    }

    /**
     * @param Collection $nurses
     *
     * @return Collection|\Illuminate\Support\Collection
     */
    public function prepareDataForCalendar(Collection $nurses)
    {
        return $nurses->map(function ($nurse) {
            return $this->prepareData($nurse);
        })->flatten(1);
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\FullCalendar;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Nurse;
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
     * @param mixed $workScheduleData
     *
     * @return Builder|Model|object|null
     */
    public function checkIfWindowsExists($workScheduleData)
    {
        $nurseInfoId     = $workScheduleData['nurse_info_id'];
        $windowTimeStart = $workScheduleData['window_time_start'];
        $windowTimeEnd   = $workScheduleData['window_time_end'];
        $windowDate      = $workScheduleData['date'];

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
        ])->first();
    }

    /**
     * @param $diffRange
     * @param $eventDate
     * @param $repeatFrequency
     * @param $holidayDates
     *
     * @return array|\Illuminate\Support\Collection
     */
    public function createRecurringDates($diffRange, $eventDate, $repeatFrequency, $holidayDates)
    {
        $defaultRecurringDates = collect();

        if ('weekly' === $repeatFrequency) {
            for ($i = 0; $i < $diffRange; ++$i) {
                $defaultRecurringDate = Carbon::parse($eventDate)->copy()->addWeek($i)->toDateString();
                //do NOT create workEvents over days-off.
                if ( ! in_array($defaultRecurringDate, $holidayDates)) {
                    $defaultRecurringDates[] = $defaultRecurringDate;
                }
            }
        }

        if ('daily' === $repeatFrequency) {
            for ($i = 0; $i < $diffRange; ++$i) { //@todo:should exclude weekedns option
                $defaultRecurringDate = Carbon::parse($eventDate)->copy()->addDay($i)->toDateString();
                //do NOT create workEvents over days-off.
                if ( ! in_array($defaultRecurringDate, $holidayDates)) {
                    $defaultRecurringDates[] = $defaultRecurringDate;
                }
            }
        }

        return $defaultRecurringDates;
    }

    /**
     * @param $nurseInfoId
     * @param $workScheduleData
     *
     * @return \Illuminate\Support\Collection
     */
    public function createRecurringEvents($nurseInfoId, $workScheduleData)
    {
        $repeatFrequency   = null === $workScheduleData['repeat_freq'] ? 'weekly' : $workScheduleData['repeat_freq'];
        $defaultRepeatDate = Carbon::parse($workScheduleData['date'])->copy()->addMonths(2)->toDateString();
        $repeatEventUntil  = null === $workScheduleData['until'] ? $defaultRepeatDate : $workScheduleData['until'];
        $rangeToRepeat     = $this->getWeeksOrDaysToRepeat($workScheduleData['date'], $repeatEventUntil, $repeatFrequency);
        $validatedDefault  = 'not_checked';
        $nurse             = Nurse::findOrFail($nurseInfoId);
        $holidays          = $nurse->upcomingHolidaysFrom(Carbon::parse($workScheduleData['date']));
        $holidayDates      = $holidays->map(function ($holiday) {
            return Carbon::parse($holiday->date)->toDateString();
        })->toArray();

        $recurringDates = $this->createRecurringDates($rangeToRepeat, $workScheduleData['date'], $repeatFrequency, $holidayDates);

        return  $this->createWindowData($recurringDates, $nurseInfoId, $workScheduleData, $validatedDefault, $repeatFrequency, $repeatEventUntil);
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
     * @param mixed $workScheduleData
     *
     * @return \Illuminate\Support\Collection
     */
    public function createWindowData(
        $defaultRecurringDates,
        $nurseInfoId,
        $workScheduleData,
        $validatedDefault,
        $defaultRepeatFreq,
        $repeatEventByDefaultUntil
    ) {
        return collect($defaultRecurringDates)->map(function ($date) use (
                $nurseInfoId,
                $workScheduleData,
                $validatedDefault,
                $defaultRepeatFreq,
                $repeatEventByDefaultUntil) {
            $newWindowDayOfWeek = Carbon::parse($date)->dayOfWeek;

            return  [
                'nurse_info_id'     => $nurseInfoId,
                'date'              => $date,
                'day_of_week'       => carbonToClhDayOfWeek($newWindowDayOfWeek),
                'window_time_start' => $workScheduleData['window_time_start'],
                'window_time_end'   => $workScheduleData['window_time_end'],
                'validated'         => $validatedDefault,
                'repeat_frequency'  => $defaultRepeatFreq,
                'repeat_start'      => Carbon::parse($workScheduleData['date'])->toDateString(),
                'until'             => $repeatEventByDefaultUntil,
                'created_at'        => Carbon::parse(now())->toDateTimeString(),
                'updated_at'        => Carbon::parse(now())->toDateTimeString(),
            ];
        });
    }

    /**
     * @return array
     */
    public function getAuthData()
    {
        $auth = auth()->user();

        if ($auth->isAdmin()) {
            return [
                'role' => 'admin',
            ];
        }

        if ($auth->isCareCoach()) {
            return [
                'role'        => 'nurse',
                'nurseInfoId' => $auth->nurseInfo->id,
            ];
        }
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
     * @param mixed $updateCollisions
     *
     * @return array
     */
    public function getEventsToAskConfirmation($recurringEventsToSave, $updateCollisions)
    {
        $askForConfirmationEvents = [];
        foreach ($recurringEventsToSave as $event) {
            $windowsExists = ! $updateCollisions ? $this->checkIfWindowsExists($event) : false;

            if ($windowsExists) {
                $askForConfirmationEvents[] = $windowsExists;
            }
        }

        return $askForConfirmationEvents;
    }

    /**
     * @param $startDate
     * @param $endDate
     *
     * @return Collection|\Illuminate\Support\Collection
     */
    public function getHolidays(Collection $nurses, $startDate, $endDate)
    { // @todo:include company holidays
        $limitDate = Carbon::parse(now())->startOfYear()->subMonth(2)->toDate();

        return $nurses->map(function ($nurse) use ($limitDate, $startDate, $endDate) {
            $holidays = $nurse->nurseInfo->upcoming_holiday_dates;

            return $this->prepareHolidaysData($holidays, $nurse, $startDate, $endDate);
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
     * @param mixed $startDate
     * @param mixed $endDate
     *
     * @return \Illuminate\Support\Collection
     */
    public function getWindows($nurse, $startDate, $endDate)
    {
        return $nurse->nurseInfo->windows->where('date', '>=', $startDate)->where('date', '<=', $endDate);
    }

    /**
     * @param mixed $startDate
     * @param mixed $endDate
     *
     * @return Collection|\Illuminate\Support\Collection
     */
    public function prepareCalendarDataForAllActiveNurses(Collection $nurses, $startDate, $endDate)
    {
        return $nurses->map(function ($nurse) use ($startDate, $endDate) {
            $windows = $this->getWindows($nurse, $startDate, $endDate);

            return $this->prepareWorkDataForEachNurse($windows, $nurse);
        })->flatten(1);
    }

    public function prepareHolidaysData($holidays, $nurse, $startDate, $endDate)
    {
        return collect($holidays)
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->map(function ($holiday) use ($nurse) {
                $holidayDate = Carbon::parse($holiday->date)->toDateString();
                $holidayDateInDayOfWeek = Carbon::parse($holidayDate)->dayOfWeek;
                $holidayInHumanLang = clhDayOfWeekToDayName($holidayDateInDayOfWeek);

                return collect(
                    [
                        self::TITLE => "$nurse->display_name day-off",
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
    }

    /**
     * @param $windows
     * @param $nurse
     *
     * @return \Illuminate\Support\Collection
     */
    public function prepareWorkDataForEachNurse($windows, $nurse)
    {
        return collect($windows)
            ->where('repeat_frequency', '!=', null)
            ->chunk(20)
            ->flatten()
            ->transform(function ($window) use ($nurse) {
                $dayInHumanLang = clhDayOfWeekToDayName($window->day_of_week);
                $windowDate = Carbon::parse($window->date)->toDateString();
                $workWeekStart = Carbon::parse($windowDate)->startOfWeek()->toDateString();
                $workHoursForDay = WorkHours::where(
                    [
                        ['workhourable_id', $nurse->nurseInfo->id],
                        ['work_week_start', $workWeekStart],
                    ]
                )->pluck($dayInHumanLang)->first();

                $windowStartForView = Carbon::parse($window->window_time_start)->format('H:i');
                $windowEndForView = Carbon::parse($window->window_time_end)->format('H:i');
                $hoursAbrev = 'h';
                $color = '#5bc0ded6';
                //@todo: dont really like this
                $title = auth()->user()->isAdmin() ? "$nurse->display_name ({$workHoursForDay}$hoursAbrev)
                        {$windowStartForView}-{$windowEndForView}" : "({$workHoursForDay}$hoursAbrev)
                        {$windowStartForView}-{$windowEndForView}";

                return collect(
                    [
                        self::TITLE        => $title,
                        self::START        => "{$windowDate}T{$window->window_time_start}",
                        self::END          => "{$windowDate}T{$window->window_time_end}",
                        'color'            => $color,
                        'textColor'        => '#fff',
                        'repeat_frequency' => $window->repeat_frequency,
                        'repeat_start'     => $window->repeat_start,
                        'until'            => $window->until,
                        'allDay'           => true,
                        'data'             => [
                            'nurseId'      => $nurse->nurseInfo->id,
                            'windowId'     => $window->id,
                            'name'         => '$nurse->display_name',
                            'day'          => $dayInHumanLang,
                            'date'         => $windowDate,
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
}

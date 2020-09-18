<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Services;

use App\LoginLogout;
use CircleLinkHealth\Customer\Services\NursesPerformanceReportService;
use CircleLinkHealth\Customer\Traits\ValidatesWorkScheduleCalendar;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Entities\WorkHours;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NurseCalendarService
{
    use ValidatesWorkScheduleCalendar;
    const COMPANY_HOLIDAY = 'companyHoliday';

    const END                = 'end';
    const FIRST_LOGIN_OF_DAY = 1;
    const NURSE_HOLIDAY      = 'nurseHoliday';
    const SATURDAY           = 6;
    const START              = 'start';
    const SUNDAY             = 0;
    const TITLE              = 'title';
    /**
     * @var string
     */
    private $cacheKey;

    /**
     * @param $diffRange
     * @param $eventDate
     * @param $repeatFrequency
     * @param $holidayDates
     * @param $excludeWeekends
     *
     * @return array|\Illuminate\Support\Collection
     */
    public function createRecurringDates($diffRange, $eventDate, $repeatFrequency, $holidayDates, $excludeWeekends)
    {
        $defaultRecurringDates = collect();

        if ('weekly' === $repeatFrequency) {
            for ($i = 0; $i <= $diffRange; ++$i) {
                $defaultRecurringDate = Carbon::parse($eventDate)->copy()->addWeek($i)->toDateString();
                //do NOT create workEvents over days-off.
                if ($excludeWeekends) {
                    if ( ! in_array($defaultRecurringDate, $holidayDates) && $this->checkIfIsNotWeekend($defaultRecurringDate)) {
                        $defaultRecurringDates[] = $defaultRecurringDate;
                    }
                } else {
                    if ( ! in_array($defaultRecurringDate, $holidayDates)) {
                        $defaultRecurringDates[] = $defaultRecurringDate;
                    }
                }
            }
        }

        if ('daily' === $repeatFrequency) {
            for ($i = 0; $i <= $diffRange; ++$i) {
                $defaultRecurringDate = Carbon::parse($eventDate)->copy()->addDay($i)->toDateString();
                //do NOT create workEvents over days-off and weekends
                if ($excludeWeekends) {
                    if ( ! in_array($defaultRecurringDate, $holidayDates) && $this->checkIfIsNotWeekend($defaultRecurringDate)) {
                        $defaultRecurringDates[] = $defaultRecurringDate;
                    }
                } else {
                    if ( ! in_array($defaultRecurringDate, $holidayDates)) {
                        $defaultRecurringDates[] = $defaultRecurringDate;
                    }
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
        $defaultRepeatDate = Carbon::parse($workScheduleData['date'])->copy()->addMonths(1)->toDateString();
        $repeatEventUntil  = null === $workScheduleData['until'] ? $defaultRepeatDate : $workScheduleData['until'];
        $rangeToRepeat     = $this->getWeeksOrDaysToRepeat($workScheduleData['date'], $repeatEventUntil, $repeatFrequency);
        $excludeWeekends   = ! empty($workScheduleData['excludeWkds']) ? $workScheduleData['excludeWkds'] : false;
        $validatedDefault  = 'not_checked';
        $nurse             = Nurse::findOrFail($nurseInfoId);

        $holidays = $nurse->upcomingHolidaysFrom(Carbon::parse($workScheduleData['date']));
        //        Using $holidayDates to avoid creating work-windows on days-off
        $holidayDates = $holidays->map(function ($holiday) {
            return Carbon::parse($holiday->date)->toDateString();
        })->toArray();

        $recurringDates = $this->createRecurringDates($rangeToRepeat, $workScheduleData['date'], $repeatFrequency, $holidayDates, $excludeWeekends);

        return $this->createWindowData($recurringDates, $nurseInfoId, $workScheduleData, $validatedDefault, $repeatFrequency, $repeatEventUntil);
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
            $repeatEventByDefaultUntil
        ) {
            $newWindowDayOfWeek = Carbon::parse($date)->dayOfWeek;

            return [
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
     * @param $auth
     *
     * @return array
     */
    public function dailyReportDataForCalendar($auth, array $dataReport, string $date)
    {
        $title = 'Daily Report';
        //            @todo:This should have been false. Changed. Should set all true in CPM_config if not true already.
        $showEfficiencyMetric       = false;
        $enableDailyReportMetrics   = false;
        $patientsCompletedRemaining = false;

        if (showNurseMetricsInDailyEmailReport($auth->id, 'efficiency_metrics')) {
            $showEfficiencyMetric = true;
        }

        if (showNurseMetricsInDailyEmailReport($auth->id, 'enable_daily_report_metrics')) {
            $enableDailyReportMetrics = true;
        }

        if (showNurseMetricsInDailyEmailReport($auth->id, 'patients_completed_and_remaining')) {
            $patientsCompletedRemaining = true;
        }

        return [
            self::TITLE => $title,
            self::START => $date,
            'allDay'    => true,
            'color'     => '#d79ef0',
            'data'      => [
                //                    'name' => $report['nurse_full_name'],
                'date'        => $date,
                'day'         => clhDayOfWeekToDayName(clhToCarbonDayOfWeek(Carbon::parse($date)->dayOfWeek)),
                'eventType'   => 'dailyReport',
                'reportData'  => $dataReport,
                'reportFlags' => [
                    'showEfficiencyMetrics'      => $showEfficiencyMetric,
                    'enableDailyReportMetrics'   => $enableDailyReportMetrics,
                    'patientsCompletedRemaining' => $patientsCompletedRemaining,
                ],
            ],
        ];
    }

    /**
     * @param $authId
     *
     * @return array
     */
    public function dailyReportsForNurse($authId)
    {
        $endDate   = now()->copy()->subDay(1);
        $startDate = $endDate->copy()->subDays(6);
        $dates     = getDatesForRange($startDate, $endDate);

        $service = new NursesPerformanceReportService();

        $reports = [];
        foreach ($dates as $date) {
            $reportForDay = $service->getDailyReportJson(Carbon::parse($date));
            if ( ! $reportForDay || ! is_json($reportForDay)) {
                $reports[$date] = [];
            } else {
                $reports[$date] = collect(json_decode($reportForDay, true))->where('nurse_id', $authId)->first();
            }
        }

        return $reports;
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
    public function getDataForDropdown($nurses)
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
            $windowsExists = ! $updateCollisions ? $this->windowsExistsValidator($event) : false;

            if ($windowsExists) {
                $askForConfirmationEvents[] = $windowsExists;
            }
        }

        return $askForConfirmationEvents;
    }

    /**
     * @param $nurses
     * @param $startDate
     * @param $endDate
     *
     * @return Collection|\Illuminate\Support\Collection
     */
    public function getHolidays($nurses, $startDate, $endDate)
    {
        return $nurses->map(function ($nurse) use ($startDate, $endDate) {
            $holidays = $nurse->nurseInfo->nurseHolidaysWithCompanyHolidays($startDate, $endDate);

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
     * @param $auth
     */
    public function getTotalVisits($auth, string $date)
    {
        return \Cache::remember("total_time_visits_for_{$auth->id}_$date", 2, function () use ($auth, $date) {
            $invoice = $auth->nurseInfo->invoices()->where('month_year', Carbon::parse($date)->startOfMonth())->first();

            return  $this->validatedInvoiceData($invoice, $auth);
        });
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
        return $nurse->nurseInfo->windows->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate);
    }

    /**
     * @return int
     */
    public function loginActivityCountFor(int $userId, Carbon $date)
    {
        return LoginLogout::where('user_id', $userId)
            ->whereBetween('login_time', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])->count();
    }

    /**
     * @return array
     */
    public function manipulateReportData(array $nextUpcomingWindow, array $report)
    {
        return  [
            'windowStart' => isset($nextUpcomingWindow['window_time_start'])
                ? Carbon::parse($nextUpcomingWindow['window_time_start'])->format('g:i A')
                : null,
            'windowEnd' => isset($nextUpcomingWindow['window_time_start'])
                ? Carbon::parse($nextUpcomingWindow['window_time_end'])->format('g:i A')
                : null,

            'nextUpcomingWindowDay' => isset($nextUpcomingWindow['window_time_start'])
                ? Carbon::parse($nextUpcomingWindow['date'])->format('l')
                : null,

            'nextUpcomingWindowMonth' => isset($nextUpcomingWindow['window_time_start'])
                ? Carbon::parse($nextUpcomingWindow['date'])->format('F d')
                : null,

            'deficitTextColor' => $report['surplusShortfallHours'] < 0
                ? '#f44336'
                : '#009688',

            'deficitOrSurplusText' => $report['surplusShortfallHours'] < 0
                ? 'Deficit'
                : 'Surplus',
        ];
    }

    /**
     * @param $cacheKey
     *
     * @return \Collection|\Illuminate\Support\Collection
     */
    public function nurseDailyReportForDate(int $userId, Carbon $date, string $cacheKey)
    {
        $this->cacheKey     = $cacheKey;
        $loginActivityCount = $this->loginActivityCountFor($userId, Carbon::now());
        $cacheTime          = Carbon::now()->endOfDay();
        if ($loginActivityCount <= self::FIRST_LOGIN_OF_DAY) {
            Cache::put($cacheKey, $cacheKey, $cacheTime);
            try {
                return $this->prepareDailyReportsForNurse(User::findOrFail($userId), $date);
            } catch (\Exception $e) {
                Log::error("User for $userId not found. Cannot prepare yesterday's daily report.");
            }
        }

        return collect();
    }

    /**
     * @param $authId
     *
     * @return array
     */
    public function nurseReportForDate($authId, Carbon $date)
    {
        $service      = new NursesPerformanceReportService();
        $reportForDay = $service->getDailyReportJson($date);

        $report = [];
        if ( ! $reportForDay || ! is_json($reportForDay)) {
            $report[$date->toDateString()] = [];
        } else {
            $report[$date->toDateString()] = collect(json_decode($reportForDay, true))->where('nurse_id', $authId)->first();
        }

        return $report;
    }

    /**
     * @param Collection $nurses
     * @param mixed      $startDate
     * @param mixed      $endDate
     *
     * @return \Illuminate\Support\Collection
     */
    public function prepareCalendarDataForAllActiveNurses($nurses, $startDate, $endDate)
    {
        return $nurses->map(function ($nurse) use ($startDate, $endDate) {
            $windows = $this->getWindows($nurse, $startDate, $endDate);

            return $this->prepareWorkDataForEachNurse($windows, $nurse);
        })->flatten(1);
    }

    /**
     * @param mixed $auth
     * @param null  $date
     *
     * @return \Collection|\Illuminate\Support\Collection
     */
    public function prepareDailyReportsForNurse($auth, $date = null)
    {
        $reports = $this->dailyReportsForNurse($auth->id);
//        ! Cache::has($this->cacheKey) exists only when debug is ON.
        if ($date) {
            $reports = $this->nurseReportForDate($auth->id, $date);
        }
        $reportsForCalendarView = [];
        foreach ($reports as $date => $report) {
            if (empty($report)) {
                continue;
            }

            $nextUpcomingWindow = [];

            if (is_array($report['nextUpcomingWindow'])) {
                $nextUpcomingWindow = $report['nextUpcomingWindow'];
            }

            $reportCalculations = $this->manipulateReportData($nextUpcomingWindow, $report);
            $dataReport         = array_merge($report, $reportCalculations);
            $totalVisits        = $this->getTotalVisits($auth, $date);

            if ( ! empty($totalVisits)) {
                $dataReport = array_merge($dataReport, $totalVisits);
            }

            if ( ! empty($report)) {
                if (App::environment(['testing', 'review'])) {
                    $reportsForCalendarView[] = $this->dailyReportDataForCalendar($auth, $dataReport, $date);
                }

                if (0 !== $report['systemTime'] && $auth->nurseInfo->hourly_rate > 1) {
                    $reportsForCalendarView[] = $this->dailyReportDataForCalendar($auth, $dataReport, $date);
                }
            }
        }

        return collect($reportsForCalendarView);
    }

    public function prepareHolidaysData(\Illuminate\Support\Collection $holidays, $nurse, string $startDate, string $endDate)
    {
        return $holidays
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->transform(function ($holiday) use ($nurse) {
                $holidayDate = Carbon::parse($holiday['date'])->toDateString();
                $holidayDateInDayOfWeek = Carbon::parse($holidayDate)->dayOfWeek;
                $holidayInHumanLang = clhDayOfWeekToDayName($holidayDateInDayOfWeek);
                $eventType = 'holiday';

                if (isset($holiday['eventType']) && self::COMPANY_HOLIDAY === $holiday['eventType']) {
                    $eventType = self::COMPANY_HOLIDAY;
                }

                $title = 'holiday' === $eventType
                    ? "$nurse->display_name day-off"
                    : $holiday['holiday_name'];

                return collect(
                    [
                        self::TITLE => $title,
                        self::START => $holidayDate,
                        'allDay'    => true,
                        'color'     => '#f5c431',
                        'data'      => [
                            'holidayId' => $holiday['id'],
                            'nurseId'   => $nurse->nurseInfo->id,
                            'name'      => $nurse->display_name,
                            'date'      => $holidayDate,
                            'day'       => $holidayInHumanLang,
                            'eventType' => $eventType,
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

                if ('not_worked' === $window->validated) {
                    $color = '#FB627A';
                }

                if ('worked' === $window->validated) {
                    $color = '#bcdfc1';
                }

                $title = auth()->user()->isAdmin() ?
                    "$nurse->display_name ({$workHoursForDay}$hoursAbrev)
                        {$windowStartForView}-{$windowEndForView}" :
                    "({$workHoursForDay}$hoursAbrev)
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
                            'name'         => "$nurse->display_name",
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

    /**
     * @param $invoice
     * @param $auth
     *
     * @return array
     */
    private function validatedInvoiceData($invoice, $auth)
    {
        if (empty($invoice)) {
            Log::warning("Invoice for nurse with user id: [$auth->id] not found.");

            return [];
        }

        if (empty($invoice->invoice_data)) {
            Log::warning("Invoice data for nurse with user id: [$auth->id] not found.");

            return [];
        }

        if (is_null($invoice->invoice_data['visitsCount'])) {
            Log::warning("Total visits for nurse with user id: [$auth->id] not found.");

            return [];
        }

        return [
            'totalVisitsCount' => $invoice->invoice_data['visitsCount'],
            'invoiceId'        => $invoice->id,
        ];
    }
}

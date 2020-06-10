<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\FullCalendar\NurseCalendarService;
use App\Jobs\CreateCalendarRecurringEventsJob;
use App\Services\NursesPerformanceReportService;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Entities\WorkHours;
use Illuminate\Database\Seeder;

class DailyNurseMetricsReportSeeder extends Seeder
{
//    This should only be used for testing ROAD 151. It does not produce any data that make sense
    /**
     * @var NurseCalendarService
     */
    private $calendarService;
    /**
     * @var string
     */
    private $endDate;
    /**
     * @var \Illuminate\Support\Carbon
     */
    private $now;
    /**
     * @var NursesPerformanceReportService
     */
    private $nursesPerformanceReportService;
    /**
     * @var string
     */
    private $startDate;

    /**
     * DailyNurseMetricsReportSeeder constructor.
     */
    public function __construct(NurseCalendarService $calendarService, NursesPerformanceReportService $nursesPerformanceReportService)
    {
        $this->calendarService                = $calendarService;
        $this->nursesPerformanceReportService = $nursesPerformanceReportService;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->now       = now();
        $this->startDate = $this->now->copy()->subDays(2)->toDateString();
        $this->endDate   = $this->now->copy()->addDays(2)->toDateString();

        $user = User::findOrFail(13329);

        if ( ! $user->primaryPractice->is_demo) {
            throw new Exception('You are not allowed to perform this action on a non-demo user');
        }

        $nurseWindows = $this->getExistingWindows($user->nurseInfo);

        if ( ! $nurseWindows->exists()) {
            $this->createWorkScheduleData($user);
        }

        $dataOnS3 = $this->nursesPerformanceReportService->showDataFromS3(\Carbon\Carbon::parse('2020-06-08'));

        if (empty($dataOnS3->first())) {
            $this->createDummyReportForYesterday($user);
        }
    }

    private function createDummyReportForYesterday(User $user)
    {
        $nextUpcomingWindow = $user->nurseInfo->firstWindowAfter(Carbon::now());

        if ($nextUpcomingWindow) {
            $carbonDate              = Carbon::parse($nextUpcomingWindow->date);
            $nextUpcomingWindowLabel = clhDayOfWeekToDayName(
                $nextUpcomingWindow->day_of_week
            )." {$carbonDate->format('m/d/Y')}";
        }

        $dataToUpload = $this->createFakeData($user, $nextUpcomingWindow, $nextUpcomingWindowLabel ?? null);

        $date     = \Carbon\Carbon::yesterday()->startOfDay();
        $fileName = "nurses-and-states-daily-report-{$date->toDateString()}.json";
        $path     = storage_path($fileName);
        $saved    = file_put_contents($path, json_encode(collect($dataToUpload)));

        SaasAccount::whereSlug('circlelink-health')
            ->first()
            ->addMedia($path)
            ->toMediaCollection($fileName);
    }

    private function createFakeData(User $user, $nextUpcomingWindow, $nextUpcomingWindowLabel)
    {
        return [collect([
            'nurse_id'                       => $user->id,
            'nurse_full_name'                => $user->display_name,
            'systemTime'                     => 100,
            'actualHours'                    => 0,
            'committedHours'                 => 0,
            'scheduledCalls'                 => 0,
            'actualCalls'                    => 0,
            'successful'                     => 0,
            'unsuccessful'                   => 0,
            'totalMonthSystemTimeSeconds'    => 0,
            'uniquePatientsAssignedForMonth' => 0,
            'completionRate'                 => 0,
            'efficiencyIndex'                => 0,
            'caseLoadComplete'               => 0,
            'caseLoadNeededToComplete'       => 0,
            'hoursCommittedRestOfMonth'      => 0,
            'avgCCMTimePerPatient'           => 0,
            'avgCompletionTime'              => 0,
            'incompletePatients'             => 0,
            'completedPatients'              => 0,
            'totalPatientsInCaseLoad'        => 0,
            'nextUpcomingWindow'             => $nextUpcomingWindow,
            'totalHours'                     => 0,
            'nextUpcomingWindowLabel'        => $nextUpcomingWindowLabel,
            'projectedHoursLeftInMonth'      => 0,
            'avgHoursWorkedLast10Sessions'   => 0,
            'surplusShortfallHours'          => 0,
        ])];
    }

    private function createWorkScheduleData(User $user)
    {
        $date      = $this->startDate;
        $dayOfWeek = \Carbon\Carbon::parse($date)->dayOfWeek;
        $user->nurseInfo()->update(['status' => 'active']);
        $nurseInfoId = $user->nurseInfo->id;

        $window = $user->nurseInfo->windows()->updateOrCreate(
            [
                'nurse_info_id' => $user->nurseInfo->id,
                'date'          => $date,
                'day_of_week'   => $dayOfWeek,
            ],
            [
                'window_time_start' => '10:00',
                'window_time_end'   => '18:00',
                'repeat_start'      => $date,
                'repeat_frequency'  => null,
                'until'             => null,
                'validated'         => 'not_checked',
            ]
        );

        $workWeekStart   = Carbon::parse($date)->startOfWeek();
        $workHoursCreate = WorkHours::updateOrCreate(
            [
                'workhourable_id' => $nurseInfoId,
            ],
            [
                'workhourable_type'                           => Nurse::class,
                'work_week_start'                             => Carbon::parse($workWeekStart)->toDateString(),
                strtolower(clhDayOfWeekToDayName($dayOfWeek)) => 5,
            ]
        );

        $eventDateToDayName = clhDayOfWeekToDayName($window->day_of_week);
        $workHours          = $window->nurse->workhourables->where('workhourable_id', $nurseInfoId)->pluck(lcfirst($eventDateToDayName))->first();

        $windowData = [
            'repeat_freq'       => 'daily',
            'date'              => $date,
            'until'             => $this->endDate,
            'window_time_start' => $window->window_time_start,
            'window_time_end'   => $window->window_time_end,
            'work_hours'        => $workHours,
        ];

        $recurringEventsToSave = $this->calendarService->createRecurringEvents($nurseInfoId, $windowData);
        CreateCalendarRecurringEventsJob::dispatch($recurringEventsToSave, $window, null, $windowData['work_hours'])->onQueue('low');
    }

    private function getExistingWindows(Nurse $nurseInfo)
    {
        return $nurseInfo->windows()
            ->where([
                ['date', '>=', $this->startDate],
                ['date', '<=', $this->now->copy()->toDateString()],
            ]);
    }
}

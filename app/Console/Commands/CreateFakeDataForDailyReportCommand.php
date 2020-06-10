<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\FullCalendar\NurseCalendarService;
use App\Jobs\CreateCalendarRecurringEventsJob;
use App\Services\NursesPerformanceReportService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Entities\WorkHours;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class CreateFakeDataForDailyReportCommand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates fake daily report for yesterday and work data for week. It is necessary for daily report modal pop-up feature';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:dailyReportFakeData {nurseUserId? : User ID of nurse to generate fake data.} ';
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
     * Create a new command instance.
     */
    public function __construct(NurseCalendarService $calendarService, NursesPerformanceReportService $nursesPerformanceReportService)
    {
        parent::__construct();
        $this->calendarService                = $calendarService;
        $this->nursesPerformanceReportService = $nursesPerformanceReportService;
    }

    /**
     * Run the database seeds.
     *
     * @throws \CircleLinkHealth\Core\Exceptions\FileNotFoundException
     * @return void
     */
    public function createNurseFakeData(User $user)
    {
        $this->now       = now();
        $this->startDate = $this->now->copy()->subDays(2)->toDateString();
        $this->endDate   = $this->now->copy()->addDays(2)->toDateString();

        $nurseWindows = $this->getExistingWindows($user->nurseInfo);

        if ( ! $nurseWindows->exists()) {
            $this->createWorkScheduleData($user);
        }

        $dataOnS3 = $this->nursesPerformanceReportService->showDataFromS3(Carbon::yesterday());

        if (empty($dataOnS3->first())) {
            $this->createDummyReportForYesterday($user);
        }
    }

    /**
     * @throws \CircleLinkHealth\Core\Exceptions\FileNotFoundException
     */
    public function handle()
    {
        $userId = $this->argument('nurseUserId') ?? null;
        $user   = $user   = User::findOrFail($userId);

        if (App::environment(['testing'])) {
            $this->createNurseFakeData($user);

            return;
        }

        if ( ! $user->primaryPractice->is_demo) {
            $this->error('You are not allowed to perform this action on a non-demo user');

            return;
        }

        if (is_null($userId)) {
            $this->error('User id is required');

            return;
        }

        $this->createNurseFakeData($user);

        $this->info("Data Generated Successfully For User $userId");
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

        $date     = Carbon::yesterday()->startOfDay();
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
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
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

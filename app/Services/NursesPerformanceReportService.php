<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Traits\NursePerformanceCalculations;
use Carbon\Carbon;
use CircleLinkHealth\Core\Exceptions\FileNotFoundException;
use CircleLinkHealth\Customer\Entities\CompanyHoliday;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\AggregatedTotalTimePerNurse;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NursesPerformanceReportService
{
    use NursePerformanceCalculations;

    const LAST_COMMITTED_DAYS_TO_GO_BACK = 10;
    const MAX_COMMITTED_DAYS_TO_GO_BACK  = 30;
    const MIN_CALL                       = 1;

    protected $aggregatedTotalTimePerNurse;

    protected $avgHoursWorkedLast10Sessions;

    protected $successfulCallsMultiplier;

    protected $timeGoal;

    protected $unsuccessfulCallsMultiplier;

    // @var CompanyHoliday[]
    private $companyHolidays;

    /**
     * @param Carbon $date This is usually yesterday's date. Assuming report runs at midnight,
     *                     and generates report for the day before
     *
     * @return Collection
     */
    public function collectData(Carbon $date)
    {
        $this->setReportSettings();

        $this->companyHolidays = CompanyHoliday::query();

        $data = [];
        User::ofType('care-center')
            ->with(
                [
                    'nurseInfo' => function ($info) {
                        $info->with(
                            [
                                'windows',
                                'holidays',
                                'workhourables',
                            ]
                        );
                    },
                ]
            )
            ->whereHas(
                'nurseInfo',
                function ($info) {
                    $info->where('status', 'active')
                        ->when(isProductionEnv(), function ($info) {
                            $info->where('is_demo', false);
                        });
                }
            )
            ->chunk(
                35,
                function ($nurses) use (&$data, $date) {
                    $aggregatedTime = new AggregatedTotalTimePerNurse(
                        $nurses->pluck('id')->all(),
                        $date->copy()->startOfDay(),
                        $date->copy()->endOfDay()
                    );

                    foreach ($nurses as $nurse) {
                        $data[] = $this->getDataForNurse($nurse, $date->copy(), $aggregatedTime->totalSystemTime($nurse->id));
                    }
                }
            );

        return collect($data);
    }

    /**
     * @return mixed
     */
    public function getDailyReportJson(Carbon $day)
    {
        return optional(
            SaasAccount::whereSlug('circlelink-health')
                ->first()
                ->getMedia("nurses-and-states-daily-report-{$day->toDateString()}.json")
                ->sortByDesc('id')
                ->first()
        )->getFile();
    }

    /**
     * @param $nurse
     * @param $date
     *
     * Sets up data needed by both Nurse and States Dashboard and EmailRNDailyReport
     */
    public function getDataForNurse(User $nurse, Carbon $date, int $totalSystemTime): Collection
    {
//        "$patientsForMonth" returns ONLY status SCHEDULED patients...
//        It's "Case Load" in UI.
        $patientsForMonth                     = $this->getUniquePatientsAssignedForNurseForMonth($nurse, $date);
        $totalMonthlyCompletedPatientsOfNurse = $this->getTotalCompletedPatientsOfNurse($patientsForMonth);
        $successfulCallsDaily                 = $nurse->countSuccessfulCallsFor($date);
        //        "Case Completion" in view
        $caseLoadComplete = $this->percentageCaseLoadComplete($patientsForMonth, $totalMonthlyCompletedPatientsOfNurse);

        $data = [
            'nurse_id'        => $nurse->id,
            'nurse_full_name' => $nurse->getFullName(),
            'systemTime'      => $totalSystemTime,
            'actualHours'     => round((float) ($totalSystemTime / 3600), 1),
            'committedHours'  => $nurse->nurseInfo->isOnHoliday($date, $this->companyHolidays)
                ? 0
                : $nurse->nurseInfo->getHoursCommittedForCarbonDate($date),
            'scheduledCalls' => $nurse->countScheduledCallsFor($date),
            //            "completed calls" in UI
            'actualCalls'                    => $nurse->countCompletedCallsFor($date),
            'successful'                     => $successfulCallsDaily,
            'unsuccessful'                   => $nurse->countUnSuccessfulCallsFor($date),
            'totalMonthSystemTimeSeconds'    => $this->getTotalMonthSystemTimeSeconds($nurse, $date),
            'uniquePatientsAssignedForMonth' => $patientsForMonth->count(),
        ];

        //new metrics
        $data['completionRate']  = $this->getCompletionRate($data);
        $data['efficiencyIndex'] = $this->getEfficiencyIndex($data);
//        "Case Completion" in view
        $data['caseLoadComplete'] = $caseLoadComplete;
//        $data['caseLoadNeededToComplete']  = $this->estHoursToCompleteCaseLoadMonth($patientsForMonth);
        $data['caseLoadNeededToComplete']  = $this->estHoursToCompleteCaseLoadMonth($nurse, $date, $patientsForMonth, $totalMonthlyCompletedPatientsOfNurse, $successfulCallsDaily);
        $data['hoursCommittedRestOfMonth'] = $this->getHoursCommittedRestOfMonth(
            $nurse,
            $nurse->nurseInfo->upcomingHolidaysFrom($date),
            $date
        );

        // V-3 metrics cpm-2085
        $data['avgCCMTimePerPatient'] = $this->estAvgCCMTimePerMonth($patientsForMonth, $totalMonthlyCompletedPatientsOfNurse);
        $data['avgCompletionTime']    = $this->getAvgCompletionTime($nurse, $date, $totalMonthlyCompletedPatientsOfNurse);
        $data['incompletePatients']   = $this->getIncompletePatientsCount($patientsForMonth);

        //only for EmailRNDailyReport
        $nextUpcomingWindow = $nurse->nurseInfo->firstWindowAfter(Carbon::now());
        //only for EmailRNDailyReport v 2
        $data['completedPatients']       = $totalMonthlyCompletedPatientsOfNurse;
        $data['totalPatientsInCaseLoad'] = $patientsForMonth->count();

        if ($nextUpcomingWindow) {
            $carbonDate              = Carbon::parse($nextUpcomingWindow->date);
            $nextUpcomingWindowLabel = clhDayOfWeekToDayName(
                $nextUpcomingWindow->day_of_week
            )." {$carbonDate->format('m/d/Y')}";
        }

        $workHours  = $nurse->nurseInfo->workhourables->first();
        $totalHours = $workHours && $nextUpcomingWindow
            ? (string) $workHours->{strtolower(
                clhDayOfWeekToDayName($nextUpcomingWindow->day_of_week)
            )}
            : null;

        $data['nextUpcomingWindow']           = $nextUpcomingWindow;
        $data['totalHours']                   = $totalHours;
        $data['nextUpcomingWindowLabel']      = $nextUpcomingWindowLabel ?? null;
        $data['projectedHoursLeftInMonth']    = $this->getProjectedHoursLeftInMonth($nurse, $date->copy()) ?? 0;
        $data['avgHoursWorkedLast10Sessions'] = $this->avgHoursWorkedLast10Sessions;
        $data['surplusShortfallHours']        = $this->surplusShortfallHours($data);

        return collect($data);
    }

    /**
     * There are no data on S3 before this date.
     *
     * @return Carbon
     */
    public function getLimitDate()
    {
        return Carbon::parse('2019-02-03');
    }

    /**
     * Data structure:
     * Nurses < Days < Data.
     *
     * @param $days
     *
     * @throws Exception
     *
     * @return Collection
     */
    public function manipulateData($days)
    {
        $reports = [];
        foreach ($days as $day) {
            try {
                $reports[$day->toDateString()] = $this->showDataFromS3($day);
            } catch (FileNotFoundException $exception) {
                $reports[$day->toDateString()] = [];
            }
        }

        $nurses  = [];
        $reports = collect($reports);
        foreach ($reports as $report) {
            if ( ! empty($report)) {
                $nurses[] = $report->pluck('nurse_full_name');
            }
        }

        $nurses = collect($nurses)
            ->flatten()
            ->unique()
            ->mapWithKeys(
                function ($nurse) use ($reports) {
                    $week = [];
                    foreach ($reports as $dayOfWeek => $reportPerDay) {
                        if ( ! empty($reportPerDay)) {
                            $week[$dayOfWeek] = collect($reportPerDay)->where('nurse_full_name', $nurse)->first();
                            if (empty($week[$dayOfWeek])) {
                                $week[$dayOfWeek] = [
                                    'nurse_full_name'                => $nurse,
                                    'committedHours'                 => 0,
                                    'actualHours'                    => 0,
                                    'unsuccessful'                   => 0,
                                    'successful'                     => 0,
                                    'actualCalls'                    => 0,
                                    'scheduledCalls'                 => 0,
                                    'efficiency'                     => 0,
                                    'completionRate'                 => 0,
                                    'efficiencyIndex'                => 0,
                                    'uniquePatientsAssignedForMonth' => 0,
                                    'caseLoadComplete'               => 0,
                                    'caseLoadNeededToComplete'       => 0,
                                    'hoursCommittedRestOfMonth'      => 0,
                                    'surplusShortfallHours'          => 0,
                                    'avgCCMTimePerPatient'           => 0,
                                    'avgCompletionTime'              => 0,
                                    'incompletePatients'             => 0,
                                ];
                            }
                        }
                    }

                    return [$nurse => $week];
                }
            );

        $totalsPerDay = [];
        foreach ($reports as $dayOfWeek => $reportPerDay) {
            $totalsPerDay[$dayOfWeek] = [
                'scheduledCalls'                 => $reportPerDay->sum('scheduledCalls'),
                'actualCalls'                    => $reportPerDay->sum('actualCalls'),
                'successful'                     => $reportPerDay->sum('successful'),
                'unsuccessful'                   => $reportPerDay->sum('unsuccessful'),
                'actualHours'                    => $reportPerDay->sum('actualHours'),
                'committedHours'                 => $reportPerDay->sum('committedHours'),
                'efficiency'                     => number_format($reportPerDay->avg('efficiency'), '2'),
                'completionRate'                 => number_format($reportPerDay->avg('completionRate'), '2'),
                'efficiencyIndex'                => number_format($reportPerDay->avg('efficiencyIndex'), '2'),
                'uniquePatientsAssignedForMonth' => number_format(
                    $reportPerDay->avg('uniquePatientsAssignedForMonth'),
                    '2'
                ),
                'caseLoadComplete'          => number_format($reportPerDay->avg('caseLoadComplete'), '2'),
                'caseLoadNeededToComplete'  => $reportPerDay->sum('caseLoadNeededToComplete'),
                'projectedHoursLeftInMonth' => number_format($reportPerDay->sum('projectedHoursLeftInMonth'), '2'),
                'hoursCommittedRestOfMonth' => $reportPerDay->sum('hoursCommittedRestOfMonth'),
                'surplusShortfallHours'     => $reportPerDay->sum('surplusShortfallHours'),
                'avgCCMTimePerPatient'      => $reportPerDay->sum('avgCCMTimePerPatient'),
                'avgCompletionTime'         => $reportPerDay->sum('avgCompletionTime'),
                'incompletePatients'        => $reportPerDay->sum('incompletePatients'),
            ];
        }

        $nursesDailyTotalsForView = $this->prepareTotalsForView($totalsPerDay);

        $nurses->put('totals', $nursesDailyTotalsForView);

        return $nurses;
    }

    /**
     * Prepares only the totals that will be used in the table.
     * 'nurse_full_name' must be named like this cause the "totals" array will be displayed in "nurse names"column.
     *
     * @param $totalsPerDay
     *
     * @return array
     */
    public function prepareTotalsForView(array $totalsPerDay)
    {
        return collect($totalsPerDay)->mapWithKeys(function ($totalsForDay, $day) {
            return [
                $day => [
                    'nurse_full_name' => 'Z - Totals for:',
                    //"Z" exists to place totals last in order.(tangy)
                    'weekDay'                        => $day,
                    'scheduledCalls'                 => $totalsForDay['scheduledCalls'],
                    'actualCalls'                    => $totalsForDay['actualCalls'],
                    'successful'                     => $totalsForDay['successful'],
                    'unsuccessful'                   => $totalsForDay['unsuccessful'],
                    'actualHours'                    => $totalsForDay['actualHours'],
                    'committedHours'                 => $totalsForDay['committedHours'],
                    'completionRate'                 => $totalsForDay['completionRate'] ?? 'N/A',
                    'efficiencyIndex'                => $totalsForDay['efficiencyIndex'] ?? 'N/A',
                    'caseLoadNeededToComplete'       => $totalsForDay['caseLoadNeededToComplete'] ?? 'N/A',
                    'projectedHoursLeftInMonth'      => $totalsForDay['projectedHoursLeftInMonth'] ?? 'N/A',
                    'hoursCommittedRestOfMonth'      => $totalsForDay['hoursCommittedRestOfMonth'] ?? 'N/A',
                    'surplusShortfallHours'          => $totalsForDay['surplusShortfallHours'] ?? 'N/A',
                    'uniquePatientsAssignedForMonth' => $totalsForDay['uniquePatientsAssignedForMonth'] ?? 'N/A',
                    'caseLoadComplete'               => $totalsForDay['caseLoadComplete'] ?? 'N/A',
                    'avgCCMTimePerPatient'           => $totalsForDay['avgCCMTimePerPatient'] ?? 'N/A',
                    'avgCompletionTime'              => $totalsForDay['avgCompletionTime'] ?? 'N/A',
                    'incompletePatients'             => $totalsForDay['incompletePatients'] ?? 'N/A',
                ],
            ];
        })->toArray();
    }

    public function setReportSettings()
    {
        $settings = DB::table('report_settings')->get();

        $nurseSuccessful   = $settings->where('name', 'nurse_report_successful')->first();
        $nurseUnsuccessful = $settings->where('name', 'nurse_report_unsuccessful')->first();
        $timeGoal          = $settings->where('name', 'time_goal_per_billable_patient')->first();

        $this->successfulCallsMultiplier = $nurseSuccessful
            ? $nurseSuccessful->value
            : '0.25';
        $this->unsuccessfulCallsMultiplier = $nurseUnsuccessful
            ? $nurseUnsuccessful->value
            : '0.067';
        $this->timeGoal = $timeGoal
            ? $timeGoal->value
            : '30';

        return true;
    }

    /**
     * @throws FileNotFoundException
     *
     * @return Collection
     */
    public function showDataFromS3(Carbon $day)
    {
        if ($day->lte($this->getLimitDate())) {
            throw new FileNotFoundException('No reports exists before this date');
        }
        $json = $this->getDailyReportJson($day);

        if ( ! $json || ! is_json($json)) {
            return collect();
        }

        return collect(json_decode($json, true));
    }
}

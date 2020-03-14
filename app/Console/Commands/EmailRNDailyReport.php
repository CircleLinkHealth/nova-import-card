<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Notifications\NurseDailyReport;
use App\Services\NursesPerformanceReportService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;

class EmailRNDailyReport extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send emails to nurses containing a report on their performance for a given date.';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nurses:emailDailyReport {date? : Date to generate report for in YYYY-MM-DD.} {nurseUserIds? : Comma separated user IDs of nurses to email report to.} ';

    private $report;
    private $service;

    /**
     * Create a new command instance.
     */
    public function __construct(NursesPerformanceReportService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * Execute the console command.
     *
     * @throws \CircleLinkHealth\Core\Exceptions\FileNotFoundException
     * @throws \Exception
     *
     * @return mixed
     */
    public function handle()
    {
        $userIds = $this->argument('nurseUserIds') ?? null;

        $date = $this->argument('date') ?? Carbon::yesterday();

        if ( ! is_a($date, Carbon::class)) {
            $date = Carbon::parse($date);
        }

        $report = $this->service->showDataFromS3($date);

        if ($report->isEmpty()) {
            \Artisan::call(NursesPerformanceDailyReport::class);
            $report = $this->service->showDataFromS3($date);
        }

        if ($report->isEmpty()) {
            $this->error('No data found for '.$date->toDateString());

            return;
        }

        $counter    = 0;
        $emailsSent = [];

        User::ofType('care-center')
            ->when(
                null != $userIds,
                function ($q) use ($userIds) {
                    $userIds = explode(',', $userIds);
                    $q->whereIn('id', $userIds);
                }
            )
            ->whereHas(
                'nurseInfo',
                function ($info) {
                    $info->where('status', 'active');
                }
            )
            ->chunk(
                10,
                function ($nurses) use (&$counter, &$emailsSent, $date, $report) {
                    foreach ($nurses as $nurse) {
                        $this->warn("Processing $nurse->id");
                        $reportDataForNurse = $report->where('nurse_id', $nurse->id)->first();
                        //In case something goes wrong with nurses and states report, or transitioning to new metrics issues
                        if ( ! $reportDataForNurse || ! $this->validateReportData($reportDataForNurse)) {
                            \Log::error("Invalid/missing report for nurse with id: {$nurse->id} and date {$date->toDateString()}");
                            continue;
                        }

                        $systemTime = $reportDataForNurse['systemTime'];

                        $totalMonthSystemTimeSeconds = $reportDataForNurse['totalMonthSystemTimeSeconds'];

                        if (0 == $systemTime) {
                            continue;
                        }

                        if ($nurse->nurseInfo->hourly_rate < 1) {
                            continue;
                        }


                        $attendanceRate = 0 != $reportDataForNurse['committedHours']
                            ? (round(
                                (float) (($reportDataForNurse['actualHours'] / $reportDataForNurse['committedHours']) * 100),
                                2
                            ))
                            : 'N/A';

                        $callsCompletionRate = 0 != $reportDataForNurse['scheduledCalls']
                            ? (0 == $reportDataForNurse['committedHours']
                                ? 'N/A'
                                : round(
                                    (float) (($reportDataForNurse['actualCalls'] / $reportDataForNurse['scheduledCalls']) * 100),
                                    2
                                ))
                            : 0;

                        $totalTimeInSystemOnGivenDate = secondsToHMS($systemTime);

                        $totalTimeInSystemThisMonth = secondsToHMS($totalMonthSystemTimeSeconds);

                        $totalEarningsThisMonth = round(
                            (float) ($totalMonthSystemTimeSeconds * $nurse->nurseInfo->hourly_rate / 60 / 60),
                            2
                        );

                        $nextUpcomingWindow = $reportDataForNurse['nextUpcomingWindow'];
                        $surplusShortfallHours = $reportDataForNurse['surplusShortfallHours'];
                        $deficitTextColor = $surplusShortfallHours < 0 ? '#f44336' : '#009688';
                        $deficitOrSurplusText = $surplusShortfallHours < 0 ? 'Deficit' : 'Surplus';

                        $data = [
                            'name'                         => $nurse->getFullName(),
                            'actualHours'                  => $reportDataForNurse['actualHours'],
                            'committedHours'               => $reportDataForNurse['committedHours'],
                            'completionRate'               => $reportDataForNurse['completionRate'],
                            'attendanceRate'               => $attendanceRate,
                            'callsCompletionRate'          => $callsCompletionRate,
                            'efficiencyIndex'              => $reportDataForNurse['efficiencyIndex'],
                            'caseLoadComplete'             => $reportDataForNurse['caseLoadComplete'],
                            'caseLoadNeededToComplete'     => $reportDataForNurse['caseLoadNeededToComplete'],
                            'hoursCommittedRestOfMonth'    => $reportDataForNurse['hoursCommittedRestOfMonth'],
                            'surplusShortfallHours'        => $surplusShortfallHours,
                            'projectedHoursLeftInMonth'    => $reportDataForNurse['projectedHoursLeftInMonth'],
                            'avgHoursWorkedLast10Sessions' => $reportDataForNurse['avgHoursWorkedLast10Sessions'],
                            'totalEarningsThisMonth'       => $totalEarningsThisMonth,
                            'totalTimeInSystemOnGivenDate' => $totalTimeInSystemOnGivenDate,
                            'totalTimeInSystemThisMonth'   => $totalTimeInSystemThisMonth,
                            'nextUpcomingWindowLabel'      => $reportDataForNurse['nextUpcomingWindowLabel'],
                            'totalHours'                   => $reportDataForNurse['totalHours'],
                            'windowStart'                  => $nextUpcomingWindow
                                ? Carbon::parse($nextUpcomingWindow['window_time_start'])->format('g:i A')
                                : null,
                            'windowEnd' => $nextUpcomingWindow
                                ? Carbon::parse($nextUpcomingWindow['window_time_end'])->format('g:i A')
                                : null,

                            //                            For new daily Report mail
                            'callsCompleted'        => $reportDataForNurse['actualCalls'],
                            'successfulCalls'       => $reportDataForNurse['successful'],
                            'completedPatients'     => $reportDataForNurse['completedPatients'],
                            'incompletePatients'    => $reportDataForNurse['incompletePatients'],
                            'avgCCMTimePerPatient'  => $reportDataForNurse['avgCCMTimePerPatient'],
                            'avgCompletionTime'     => $reportDataForNurse['avgCompletionTime'],
                            'nextUpcomingWindowDay' => $nextUpcomingWindow
                                ? Carbon::parse($nextUpcomingWindow['date'])->format('l')
                                : null,
                            'nextUpcomingWindowMonth' => $nextUpcomingWindow
                                ? Carbon::parse($nextUpcomingWindow['date'])->format('F d')
                                : null,
                            'deficitTextColor'     => $deficitTextColor,
                            'deficitOrSurplusText' => $deficitOrSurplusText,
                        ];

                        $nurse->notify(new NurseDailyReport($data, $date));

                        $this->warn("Notified $nurse->id");

                        $emailsSent[] = [
                            'nurse' => $nurse->getFullName(),
                            'email' => $nurse->email,
                        ];

                        ++$counter;
                    }
                }
            );

        $this->table(
            [
                'nurse',
                'email',
            ],
            $emailsSent
        );

        $this->info("${counter} email(s) sent.");
    }

    private function validateReportData($report)
    {
        return array_keys_exist([
            'systemTime',
            'totalMonthSystemTimeSeconds',
            'completionRate',
            'efficiencyIndex',
            'committedHours',
            'caseLoadComplete',
            'caseLoadNeededToComplete',
            'hoursCommittedRestOfMonth',
            'surplusShortfallHours',
            'nextUpcomingWindow',
            'projectedHoursLeftInMonth',
            'avgHoursWorkedLast10Sessions',
        ], $report);
    }
}

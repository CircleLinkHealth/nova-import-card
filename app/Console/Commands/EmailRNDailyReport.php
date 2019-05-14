<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Notifications\NurseDailyReport;
use App\Services\NursesAndStatesDailyReportService;
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
    protected $signature = 'nurses:emailDailyReport {nurseUserIds? : Comma separated user IDs of nurses to email report to.} {date? : Date to generate report for in YYYY-MM-DD.}';

    private $report;
    private $service;

    /**
     * Create a new command instance.
     */
    public function __construct(NursesAndStatesDailyReportService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * Execute the console command.
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

        $this->report = $this->service->showDataFromS3($date);

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
            //trying this out instead of passing variables through Kernel
            ->whereIn('id', [11321, 8151, 1920])
            ->whereHas(
                'nurseInfo',
                function ($info) {
                    $info->where('status', 'active');
                }
            )
            ->chunk(
                20,
                function ($nurses) use (&$counter, &$emailsSent, $date) {
                    foreach ($nurses as $nurse) {
                        $reportDataForNurse = $this->report->where('nurse_id', $nurse->id)->first();

                        //In case something goes wrong with nurses and states report, or transitioning to new metrics issues
                        if ( ! $reportDataForNurse || ! $this->validateReportData($reportDataForNurse)) {
                            \Log::error("Invalid/missing report for nurse with id: {$nurse->id} and date {$date->toDateString()}");
                            //Just in case Logging does not work?
                            throw new \Exception("Invalid/missing report for nurse with id: {$nurse->id} and date {$date->toDateString()}", 500);
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

                        $totalTimeInSystemOnGivenDate = secondsToHMS($systemTime);

                        $totalTimeInSystemThisMonth = secondsToHMS($totalMonthSystemTimeSeconds);

                        $totalEarningsThisMonth = round(
                            (float) ($totalMonthSystemTimeSeconds * $nurse->nurseInfo->hourly_rate / 60 / 60),
                            2
                        );

                        $nextUpcomingWindow = $reportDataForNurse['nextUpcomingWindow'];

                        if ($nextUpcomingWindow) {
                            $carbonDate = Carbon::parse($nextUpcomingWindow['date']);
                            $nextUpcomingWindowLabel = clhDayOfWeekToDayName(
                                $nextUpcomingWindow['day_of_week']
                                                       )." {$carbonDate->format('m/d/Y')}";
                        }

                        $data = [
                            'name'                         => $nurse->getFullName(),
                            'completionRate'               => $reportDataForNurse['completionRate'],
                            'efficiencyIndex'              => $reportDataForNurse['efficiencyIndex'],
                            'caseLoadComplete'             => $reportDataForNurse['caseLoadComplete'],
                            'caseLoadNeededToComplete'     => $reportDataForNurse['caseLoadNeededToComplete'],
                            'hoursCommittedRestOfMonth'    => $reportDataForNurse['hoursCommittedRestOfMonth'],
                            'surplusShortfallHours'        => $reportDataForNurse['surplusShortfallHours'],
                            'totalEarningsThisMonth'       => $totalEarningsThisMonth,
                            'totalTimeInSystemOnGivenDate' => $totalTimeInSystemOnGivenDate,
                            'totalTimeInSystemThisMonth'   => $totalTimeInSystemThisMonth,
                            'nextWindowCarbonDate'         => $carbonDate ?? null,
                            'nextUpcomingWindowLabel'      => $nextUpcomingWindowLabel ?? null,
                            'totalHours'                   => $reportDataForNurse['committedHours'],
                            'windowStart'                  => $nextUpcomingWindow
                                ? Carbon::parse($nextUpcomingWindow['window_time_start'])->format('g:i A T')
                                : null,
                            'windowEnd' => $nextUpcomingWindow
                                ? Carbon::parse($nextUpcomingWindow['window_time_end'])->format('g:i A T')
                                : null,
                        ];

                        $nurse->notify(new NurseDailyReport($data, $date));

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
        ], $report);
    }
}

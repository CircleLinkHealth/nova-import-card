<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Notifications\NurseDailyReport;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendDailyReportToRN implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * @var Carbon
     */
    public $date;
    /**
     * @var User
     */
    public $nurseUser;
    /**
     * @var array
     */
    public $reportDataForNurse;

    /**
     * Create a new job instance.
     */
    public function __construct(User $nurseUser, Carbon $date, array $reportDataForNurse)
    {
        $this->date               = $date;
        $this->reportDataForNurse = $reportDataForNurse;
        $this->nurseUser          = $nurseUser;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //In case something goes wrong with nurses and states report, or transitioning to new metrics issues
        if ( ! $this->reportDataForNurse || ! $this->validateReportData($this->reportDataForNurse)) {
            \Log::error(
                "Invalid report for nurse with id: {$this->nurseUser->id} and date {$this->date->toDateString()}"
            );

            return;
        }

        $systemTime = $this->reportDataForNurse['systemTime'];

        $totalMonthSystemTimeSeconds = $this->reportDataForNurse['totalMonthSystemTimeSeconds'];

        if (0 == $systemTime) {
            return;
        }

        if ($this->nurseUser->nurseInfo->hourly_rate < 1) {
            return;
        }

        $attendanceRate = 0 != $this->reportDataForNurse['committedHours']
            ? (round(
                (float) (($this->reportDataForNurse['actualHours'] / $this->reportDataForNurse['committedHours']) * 100),
                2
            ))
            : 'N/A';

        $callsCompletionRate = 0 != $this->reportDataForNurse['scheduledCalls']
            ? (0 == $this->reportDataForNurse['committedHours']
                ? 'N/A'
                : round(
                    (float) (($this->reportDataForNurse['actualCalls'] / $this->reportDataForNurse['scheduledCalls']) * 100),
                    2
                ))
            : 0;

        $totalTimeInSystemOnGivenDate = secondsToHMS($systemTime);

        $totalTimeInSystemThisMonth = secondsToHMS($totalMonthSystemTimeSeconds);

        $totalEarningsThisMonth = round(
            (float) ($totalMonthSystemTimeSeconds * $this->nurseUser->nurseInfo->hourly_rate / 60 / 60),
            2
        );

        $nextUpcomingWindow    = $this->reportDataForNurse['nextUpcomingWindow'];
        $surplusShortfallHours = $this->reportDataForNurse['surplusShortfallHours'];
        $deficitTextColor      = $surplusShortfallHours < 0
            ? '#f44336'
            : '#009688';
        $deficitOrSurplusText = $surplusShortfallHours < 0
            ? 'Deficit'
            : 'Surplus';

        $data = [
            'nurseUserId'                  => $this->nurseUser->id,
            'name'                         => $this->nurseUser->getFullName(),
            'actualHours'                  => $this->reportDataForNurse['actualHours'],
            'committedHours'               => $this->reportDataForNurse['committedHours'],
            'completionRate'               => $this->reportDataForNurse['completionRate'],
            'attendanceRate'               => $attendanceRate,
            'callsCompletionRate'          => $callsCompletionRate,
            'efficiencyIndex'              => $this->reportDataForNurse['efficiencyIndex'],
            'caseLoadComplete'             => $this->reportDataForNurse['caseLoadComplete'],
            'caseLoadNeededToComplete'     => $this->reportDataForNurse['caseLoadNeededToComplete'],
            'hoursCommittedRestOfMonth'    => $this->reportDataForNurse['hoursCommittedRestOfMonth'],
            'surplusShortfallHours'        => $surplusShortfallHours,
            'projectedHoursLeftInMonth'    => $this->reportDataForNurse['projectedHoursLeftInMonth'],
            'avgHoursWorkedLast10Sessions' => $this->reportDataForNurse['avgHoursWorkedLast10Sessions'],
            'totalEarningsThisMonth'       => $totalEarningsThisMonth,
            'totalTimeInSystemOnGivenDate' => $totalTimeInSystemOnGivenDate,
            'totalTimeInSystemThisMonth'   => $totalTimeInSystemThisMonth,
            'nextUpcomingWindowLabel'      => $this->reportDataForNurse['nextUpcomingWindowLabel'],
            'totalHours'                   => $this->reportDataForNurse['totalHours'],
            'windowStart'                  => $nextUpcomingWindow
                ? Carbon::parse($nextUpcomingWindow['window_time_start'])->format('g:i A')
                : null,
            'windowEnd' => $nextUpcomingWindow
                ? Carbon::parse($nextUpcomingWindow['window_time_end'])->format('g:i A')
                : null,

            //                            For new daily Report mail
            'callsCompleted'          => $this->reportDataForNurse['actualCalls'],
            'successfulCalls'         => $this->reportDataForNurse['successful'],
            'completedPatients'       => $this->reportDataForNurse['completedPatients'],
            'incompletePatients'      => $this->reportDataForNurse['incompletePatients'],
            'avgCCMTimePerPatient'    => $this->reportDataForNurse['avgCCMTimePerPatient'],
            'avgCompletionTime'       => $this->reportDataForNurse['avgCompletionTime'],
            'totalPatientsInCaseLoad' => $this->reportDataForNurse['totalPatientsInCaseLoad'],
            'nextUpcomingWindowDay'   => $nextUpcomingWindow
                ? Carbon::parse($nextUpcomingWindow['date'])->format('l')
                : null,
            'nextUpcomingWindowMonth' => $nextUpcomingWindow
                ? Carbon::parse($nextUpcomingWindow['date'])->format('F d')
                : null,
            'deficitTextColor'     => $deficitTextColor,
            'deficitOrSurplusText' => $deficitOrSurplusText,
        ];

        $this->nurseUser->notify(new NurseDailyReport($data, $this->date));
    }

    private function validateReportData(array $report): bool
    {
        return array_keys_exist(
            [
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
            ],
            $report
        );
    }
}

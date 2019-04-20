<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Notifications\NurseDailyReport;
use App\Services\NursesAndStatesDailyReportService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Illuminate\Console\Command;

class EmailRNDailyReport extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nurses:emailDailyReport {nurseUserIds? : Comma separated user IDs of nurses to email report to.}
                                                    {date? : Date to generate report for in YYYY-MM-DD.}
                                                    ';

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
            ->chunk(
                20,
                function ($nurses) use (&$counter, &$emailsSent, $date) {
                    foreach ($nurses as $nurse) {
                        if ( ! $nurse->nurseInfo) {
                            continue;
                        }

                        $reportDataForNurse = $this->report->where('nurse_id', $nurse->id)->first();

                        $systemTime = $reportDataForNurse['systemTime'];

                        $totalMonthSystemTimeSeconds = $reportDataForNurse['totalMonthSystemTimeSeconds'];

                        if (0 == $systemTime) {
                            continue;
                        }

                        if ($nurse->nurseInfo->hourly_rate < 1
                            && 'active' != $nurse->nurseInfo
                        ) {
                            continue;
                        }

                        $totalTimeInSystemOnGivenDate = secondsToHMS($systemTime);

                        $totalTimeInSystemThisMonth = secondsToHMS($totalMonthSystemTimeSeconds);

                        $totalEarningsThisMonth = round(
                            (float) ($totalMonthSystemTimeSeconds * $nurse->nurseInfo->hourly_rate / 60 / 60),
                            2
                        );

                        $nextUpcomingWindow = $nurse->nurseInfo->firstWindowAfter(Carbon::now());

                        if ($nextUpcomingWindow) {
                            $carbonDate = Carbon::parse($nextUpcomingWindow->date);
                            $nextUpcomingWindowLabel = clhDayOfWeekToDayName(
                                $nextUpcomingWindow->day_of_week
                                                       )." {$carbonDate->format('m/d/Y')}";
                        }

                        $hours = $nurse->nurseInfo->workhourables
                            ? $nurse->nurseInfo->workhourables->first()
                            : null;

                        $totalHours = $hours && $nextUpcomingWindow
                            ? (string) $hours->{strtolower(
                                clhDayOfWeekToDayName($nextUpcomingWindow->day_of_week)
                            )}
                            : null;

                        $data = [
                            'name'           => $nurse->getFullName(),
                            'completionRate' => array_key_exists('completionRate', $reportDataForNurse)
                                ? $reportDataForNurse['completionRate']
                                : 'N/A',
                            'efficiencyIndex' => array_key_exists('efficiencyIndex', $reportDataForNurse)
                                ? $reportDataForNurse['efficiencyIndex']
                                : 'N/A',
                            'hoursBehind' => array_key_exists('hoursBehind', $reportDataForNurse)
                                ? $reportDataForNurse['hoursBehind']
                                : 'N/A',
                            'totalEarningsThisMonth'       => $totalEarningsThisMonth,
                            'totalTimeInSystemOnGivenDate' => $totalTimeInSystemOnGivenDate,
                            'totalTimeInSystemThisMonth'   => $totalTimeInSystemThisMonth,
                            'nextUpcomingWindow'           => $nextUpcomingWindow,
                            'nextWindowCarbonDate'         => $carbonDate ?? null,
                            'hours'                        => $hours,
                            'nextUpcomingWindowLabel'      => $nextUpcomingWindowLabel ?? null,
                            'totalHours'                   => $totalHours,
                            'windowStart'                  => $nextUpcomingWindow
                                ? Carbon::parse($nextUpcomingWindow->window_time_start)->format('g:i A T')
                                : null,
                            'windowEnd' => $nextUpcomingWindow
                                ? Carbon::parse($nextUpcomingWindow->window_time_end)->format('g:i A T')
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
}

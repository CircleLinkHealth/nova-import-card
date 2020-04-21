<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Notifications\NurseDailyReportToDeprecate;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Illuminate\Console\Command;

class EmailRNDailyReportToDeprecate extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is the old report.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nurses:emailDailyReportToDeprecate {nurseUserIds? : Comma separated user IDs of nurses to email report to.}
                                                    {date? : Date to generate report for in YYYY-MM-DD.}
                                                    ';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $userIds = $this->argument('nurseUserIds') ?? null;
        $date    = $this->argument('date') ?? Carbon::yesterday();

        if ( ! is_a($date, Carbon::class)) {
            $date = Carbon::parse($date);
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
            ->chunk(
                20,
                function ($nurses) use (&$counter, &$emailsSent, $date) {
                    foreach ($nurses as $nurse) {
                        if ( ! $nurse->nurseInfo) {
                            continue;
                        }
                        $activityTime = Activity::createdBy($nurse)
                            ->createdOn($date, 'performed_at')
                            ->sum('duration');

                        $systemTime = PageTimer::where('provider_id', $nurse->id)
                            ->createdOn($date, 'start_time')
                            ->sum('billable_duration');

                        $totalMonthSystemTimeSeconds = PageTimer::where('provider_id', $nurse->id)
                            ->createdInMonth($date, 'start_time')
                            ->sum('billable_duration');

                        if (0 == $systemTime) {
                            continue;
                        }

                        if ($nurse->nurseInfo->hourly_rate < 1
                            && 'active' != $nurse->nurseInfo
                        ) {
                            continue;
                        }

                        $performance = round((float) ($activityTime / $systemTime) * 100);

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
                            'name'                         => $nurse->getFullName(),
                            'performance'                  => $performance,
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

                        $nurse->notify(new NurseDailyReportToDeprecate($data, $date));

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

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Notifications\ReportGenerated;
use App\Repositories\Cache\UserNotificationList;
use App\Repositories\OpsDashboardPatientEloquentRepository;
use App\Services\OpsDashboardService;
use Carbon\Carbon;
use CircleLinkHealth\Core\Exports\FromArray;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateOpsDashboardCSVReport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    const RECEIVES_DAILY_OPS_DASHBOARD_NOTIFICATION_NOVA_KEY = 'receives_nurse_daily_ops_dashboard_notification';
    /**
     * @var Carbon
     */
    protected $date;

    /**
     * @var OpsDashboardService
     */
    protected $service;

    /**
     * @var User
     */
    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user    = $user;
        $this->service = new OpsDashboardService(new OpsDashboardPatientEloquentRepository());
        $this->date    = Carbon::now();
    }

    public function calculateDailyTotalRow($rows)
    {
        $totalCounts = [];

        foreach ($rows as $row) {
            foreach ($row as $key => $value) {
                $totalCounts[$key][] = $value;
            }
        }
        foreach ($totalCounts as $key => $value) {
            $totalCounts[$key] = array_sum($value);
        }

        return $totalCounts;
    }

    /**
     * Execute the job.
     *
     * @throws \Exception
     */
    public function handle()
    {
        ini_set('memory_limit', '512M');

        $practices = Practice::select(['id', 'display_name'])
            ->activeBillable()
            ->opsDashboardQuery($this->date->copy()->startOfMonth(), $this->date->copy()->subDay()->setTimeFromTimeString('23:30'))
            ->get()
            ->sortBy('display_name');

        $hoursBehind = $this->service->calculateHoursBehind($this->date, $practices);

        foreach ($practices as $practice) {
            $row = $this->service->dailyReportRow($practice->patients->unique('id'), $this->date);
            if (null != $row) {
                $practiceStatsMap[$practice->display_name] = $row;
            }
        }
        $practiceStatsMap['CircleLink Total'] = $this->calculateDailyTotalRow($practiceStatsMap);
        $practiceStatsMap                     = collect($practiceStatsMap);

        $fileName = "CLH-Ops-CSV-Report-{$this->date->format('Y-m-d-H:i:s')}.xls";

        $reportRows = collect();

        $reportRows->push(["Ops Report from: {$this->date->copy()->subDay()->setTimeFromTimeString('23:30')->toDateTimeString()} to: {$this->date->toDateTimeString()}"]);
        $reportRows->push(["HoursBehind: {$hoursBehind}"]);

        //empty row
        $reportRows->push(['']);

        $reportRows->push([
            'Active Accounts',
            '0 mins',
            '0-5',
            '5-10',
            '10-15',
            '15-20',
            '20+',
            '20+ BHI',
            'Total',
            'Prior Day Totals',
            'Added',
            'Unreachable',
            'Paused',
            'Withdrawn',
            'Delta',
            'G0506 To Enroll',
        ]);

        foreach ($practiceStatsMap as $key => $value) {
            $reportRows->push(
                [
                    $key,
                    $value['0 mins'],
                    $value['0-5'],
                    $value['5-10'],
                    $value['10-15'],
                    $value['15-20'],
                    $value['20+'],
                    $value['20+ BHI'],
                    $value['Total'],
                    $value['Prior Day totals'],
                    $value['Added'],
                    '-'.$value['Unreachable'],
                    '-'.$value['Paused'],
                    '-'.$value['Withdrawn'],
                    $value['Delta'],
                    $value['G0506 To Enroll'],
                ]
            );
        }

        $report          = (new FromArray($fileName, $reportRows->all(), []));
        $mediaCollection = "CLH-Ops-CSV-Reports-{$this->date->toDateString()}";
        $media           = $report->storeAndAttachMediaTo($this->user->saasAccount, $mediaCollection);

        $link = $media->getUrl();

        if (isProductionEnv()) {
            $receivers = User::whereIn('id', function ($q) {
                $q->select('config_value')
                    ->from('app_config')
                    ->where('config_key', self::RECEIVES_DAILY_OPS_DASHBOARD_NOTIFICATION_NOVA_KEY);
            })->get()->each(function ($user) use ($link) {
                $user->notify(new ReportGenerated($this->date, $link, 'Ops Dashboard'));
            });
        }
        $userNotification = new UserNotificationList($this->user);

        $userNotification->push(
            'Ops Dashboard CSV report',
            "Ops Dashboard CSV report for {$this->date->toDateTimeString()}",
            $media->getUrl(),
            'Go to page'
        );
    }
}

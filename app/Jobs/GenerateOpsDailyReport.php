<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Charts\OpsChart;
use App\Services\OpsDashboardService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateOpsDailyReport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    const MEMORY_LIMIT = '800M';

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * @var Carbon
     */
    private $date;

    /**
     * @var Carbon
     */
    private $fromDate;

    /**
     * Create a new job instance.
     */
    public function __construct(Carbon $date = null)
    {
        if ( ! $date) {
            $date = Carbon::now();
        }

        $this->date = $date;

        //If the job was not run between 23:30-23:59 we need to get revisions from 23:30, 2 days before.
        //Example: we need data for 12/5 23:30 - 12/6 23:30 (time the report was supposed to run). If the job runs at 12/7 04:25,
        //we need fromDate = date->subDay(2)->setTimeFromTimeString('23:30')
        //
        //Even though this will make the report more accurate, it still makes the report not agree with the next day report (if that was ran at the designated time.
        //Example: if the report gets data for 12/5 23:30 - 12/7 02:30, the next day report will get data for 12/6 23:30 - 12/7 23:30.
        //Thus changes between 12/6 23:30 and 12/7 2:30 will be calculated in both reports, making (total added/lost patients and prior day totals have potential discrepancies)
        if ($this->date->gte($this->date->copy()->setTimeFromTimeString('00:00')) && $this->date->lte($this->date->copy()->setTimeFromTimeString('23:29'))) {
            $this->fromDate = $this->date->copy()->subDay(2)->setTimeFromTimeString('23:30');
        } else {
            $this->fromDate = $this->date->copy()->subDay()->setTimeFromTimeString('23:30');
        }
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
     */
    public function handle(OpsDashboardService $opsDashboardService)
    {
        ini_set('memory_limit', self::MEMORY_LIMIT);
        ini_set('max_input_time', $this->timeout);
        ini_set('max_execution_time', $this->timeout);

        $demoPracticeIds = Practice::whereIsDemo(1)->pluck('id')->toArray();

        $practices = Practice::select(['id', 'display_name'])
            ->activeBillable()
            ->opsDashboardQuery($this->date->copy()->startOfMonth(), $this->fromDate)
            ->get()
            ->sortBy('display_name');

        $hoursBehind = $opsDashboardService->calculateHoursBehind($this->date, $practices);

        foreach ($practices as $practice) {
            $row = $opsDashboardService->dailyReportRow($practice->patients->unique('id'), $this->date);
            if (null != $row) {
                $rows[$practice->display_name] = $row;
            }
        }
        $rows['CircleLink Total'] = $this->calculateDailyTotalRow($rows);
        $rows                     = collect($rows);

        $data = [
            'hoursBehind'   => $hoursBehind,
            'rows'          => $rows,
            'dateGenerated' => $this->date->toDateTimeString(),
        ];

        $path = storage_path("ops-daily-report-{$this->date->toDateString()}.json");

        $saved = file_put_contents($path, json_encode($data));

        if ( ! $saved) {
            if (isProductionEnv()) {
                sendSlackMessage(
                    '#carecoach_ops',
                    "Daily Call Center Operations Report for {$this->date->toDateString()} could not be created. \n"
                );
            }
        }
        SaasAccount::whereSlug('circlelink-health')
            ->first()
            ->addMedia($path)
            ->toMediaCollection("ops-daily-report-{$this->date->toDateString()}.json");

        \Cache::forget(OpsChart::ADMIN_CHART_CACHE_KEY);

        if (isProductionEnv()) {
            sendSlackMessage(
                '#carecoach_ops',
                "Daily Call Center Operations Report for {$this->date->toDateString()} created. \n"
            );
        }
    }
}

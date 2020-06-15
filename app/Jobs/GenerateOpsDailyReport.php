<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Charts\OpsChart;
use App\Services\OpsDashboardReport;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\OpsDashboardPracticeReport;
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
    public $tries = 5;

    /**
     * @var Carbon
     */
    private $date;

    /**
     * @var Carbon
     */
    private $fromDate;

    /**
     * @var array
     */
    private $practiceIds;

    /**
     * Create a new job instance.
     */
    public function __construct(array $practiceIds, Carbon $date = null)
    {
        if ( ! $date) {
            $date = Carbon::now();
        }

        $this->practiceIds = $practiceIds;

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
     *
     * @throws \Exception
     */
    public function handle(OpsDashboardReport $opsDashboardService)
    {
        ini_set('memory_limit', self::MEMORY_LIMIT);
        ini_set('max_input_time', $this->timeout);
        ini_set('max_execution_time', $this->timeout);

        $reports = OpsDashboardPracticeReport::with('practice')
            ->whereIn('practice_id', $this->practiceIds)
            ->where('date', $this->date->toDateString())
            ->get()
            ->sortBy(function ($r) {
                return $r->practice->display_name;
            });

        $pendingReports = $reports->where('is_processed', false)->count();

        if ($pendingReports > 0) {
            //push back to queue
            if (5 == $this->attempts()) {
                throw new \Exception('Some Jobs for Practices are not being processed. Unable to create Ops Daily Report.');
            }

            $this->release(300);

            return;
        }

        $reports = $reports->where('data', '!=', null);

        if (0 == $reports->count()) {
            return;
        }
        //get vars for hours behind
        $totalEnrolledPatientsCount = $reports->sum(function ($report) {
            if (empty($report->data)) {
                return 0;
            }

            return $report->data['Total'];
        });

        $totalPatientCcmTime = $reports->sum(function ($report) {
            if (empty($report->data)) {
                return 0;
            }

            return $report->data['total_ccm_time'];
        });

        $hoursBehind = $opsDashboardService->calculateHoursBehind($this->date, $totalEnrolledPatientsCount, $totalPatientCcmTime);

        foreach ($reports as $report) {
            $row = $report->data;
            if (null != $row) {
                $rows[$report->practice->display_name] = $row;
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

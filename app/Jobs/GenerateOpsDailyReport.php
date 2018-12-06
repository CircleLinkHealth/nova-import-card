<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Practice;
use App\SaasAccount;
use App\Services\OpsDashboardService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateOpsDailyReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Carbon
     */
    private $date;

    /**
     * Create a new job instance.
     *
     * @param Carbon|null $date
     */
    public function __construct(Carbon $date = null)
    {
        if ( ! $date) {
            $date = Carbon::now();
        }

        $this->date = $date;
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
     * @param OpsDashboardService $opsDashboardService
     */
    public function handle(OpsDashboardService $opsDashboardService)
    {
        ini_set('memory_limit', '512M');

        $practices = Practice::select(['id', 'display_name'])
            ->activeBillable()
            ->with([
                'patients' => function ($p) {
                    $p->with([
                        'patientSummaries' => function ($s) {
                            $s->where('month_year', $this->date->copy()->startOfMonth());
                        },
                        'patientInfo.revisionHistory' => function ($r) {
                            $r->where('key', 'ccm_status')
                                ->where(
                                  'created_at',
                                  '>=',
                                  $this->date->copy()->subDay()->setTimeFromTimeString('23:30')
                              );
                        },
                    ]);
                },
            ])
            ->whereHas('patients.patientInfo')
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
            if (app()->environment('worker')) {
                sendSlackMessage(
                    '#callcenter_ops',
                    "Daily Call Center Operations Report for {$this->date->toDateString()} could not be created. \n"
                );
            }
        }

        SaasAccount::whereSlug('circlelink-health')
            ->first()
            ->addMedia($path)
            ->toMediaCollection("ops-daily-report-{$this->date->toDateString()}.json");

        if (app()->environment('worker')) {
            sendSlackMessage(
                '#callcenter_ops',
                "Daily Call Center Operations Report for {$this->date->toDateString()} created. \n"
            );
        }
    }
}

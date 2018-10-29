<?php

namespace App\Jobs;

use App\Patient;
use App\Practice;
use App\Repositories\OpsDashboardPatientEloquentRepository;
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

    protected $service;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->service = new OpsDashboardService(new OpsDashboardPatientEloquentRepository());
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $date = Carbon::now();

        ini_set('memory_limit','512M');

        $practices = Practice::select(['id', 'display_name'])
                             ->activeBillable()
                             ->with([
                                 'patients' => function ($p) use ($date) {
                                     $p->with([
                                         'patientSummaries'            => function ($s) use ($date) {
                                             $s->where('month_year', $date->copy()->startOfMonth());
                                         },
                                         'patientInfo.revisionHistory' => function ($r) use ($date) {
                                             $r->where('key', 'ccm_status')
                                               ->where('created_at', '>=',
                                                   $date->copy()->subDay()->setTimeFromTimeString('23:00'));
                                         },
                                     ]);
                                 },
                             ])
                             ->whereHas('patients.patientInfo')
                             ->get()
                             ->sortBy('display_name');



        $hoursBehind = $this->service->calculateHoursBehind($date, $practices);

        foreach ($practices as $practice) {
            $row = $this->service->dailyReportRow($practice->patients->unique('id'), $date);
            if ($row != null) {
                $rows[$practice->display_name] = $row;
            }
        }
        $rows['CircleLink Total'] = $this->calculateDailyTotalRow($rows);
        $rows                     = collect($rows);

        $data = [
            'hoursBehind' => $hoursBehind,
            'rows'        => $rows,
        ];

        $path = storage_path("ops-daily-report-{$date->toDateString()}.json");

        $saved = file_put_contents($path, json_encode($data));

        if ( ! $saved) {
            if (app()->environment('worker')) {
                sendSlackMessage('#callcenter_ops',
                    "Daily Call Center Operations Report for {$date->toDateString()} could not be created. \n");
            }
        }

        SaasAccount::whereSlug('circlelink-health')
                   ->first()
                   ->addMedia($path)
                   ->toMediaCollection("ops-daily-report-{$date->toDateString()}.json");

        if (app()->environment('worker')) {
            sendSlackMessage('#callcenter_ops',
                "Daily Call Center Operations Report for {$date->toDateString()} created. \n");
        }
    }


    public function calculateDailyTotalRow($rows)
    {
        $totalCounts = [];

        foreach ($rows as $row){
            foreach ($row as $key => $value){
                $totalCounts[$key][] = $value;
            }

        }
        foreach($totalCounts as $key => $value){

            $totalCounts[$key] = array_sum($value);
        }

        return $totalCounts;

    }
}

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

        $practices = Practice::activeBillable()
                             ->with([
                                 'patients' => function ($p) use ($date) {
                                     $p->with([
                                         'activities'      => function ($a) use ($date) {
                                             $a->where('performed_at', '>=',
                                                 $date->copy()->startOfMonth()->startOfDay());
                                         },
                                         'revisionHistory' => function ($r) use ($date) {
                                             $r->where('key', 'ccm_status')
                                               ->where('created_at', '>=', $date->copy()->subDay());
                                         },
                                         'patientInfo',
                                     ]);
                                 },
                             ])
                             ->whereHas('patients.patientInfo')
                             ->get()
                             ->sortBy('display_name');

        $enrolledPatients = $practices->map(function ($practice) {
            return $practice->patients->filter(function ($user) {
                if (!$user) {
                    return false;
                }
                if(!$user->patientInfo) {
                    return false;
                }
                return $user->patientInfo->ccm_status == Patient::ENROLLED;
            });
        })->flatten()->unique('id');

        $hoursBehind = $this->service->calculateHoursBehind($date, $enrolledPatients);

        foreach ($practices as $practice) {

            $row = $this->service->dailyReportRow($practice->patients->unique('id'),
                $enrolledPatients->where('program_id', $practice->id), $date);
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

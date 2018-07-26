<?php

namespace App\Jobs;

use App\Patient;
use App\Practice;
use App\Repositories\OpsDashboardPatientEloquentRepository;
use App\Services\OpsDashboardService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
                                         'activities' => function ($a) use ($date) {
                                             $a->where('performed_at', '>=',
                                                 $date->copy()->startOfMonth()->startOfDay())
                                               ->where('performed_at', '<=', $date);
                                         },
                                         'patientInfo',
                                     ]);
                                 },
                             ])
                             ->whereHas('patients.patientInfo', function ($p) {
                                 $p->where('ccm_status', Patient::ENROLLED)
                                   ->orWhere('ccm_status', Patient::PAUSED)
                                   ->orWhere('ccm_status', Patient::WITHDRAWN);
                             })
                             ->get()
                             ->sortBy('display_name');

        $enrolledPatients = $practices->map(function ($practice) {
            return $practice->patients->map(function ($user) {
                if ($user->patientInfo->ccm_status == Patient::ENROLLED) {
                    return $user;
                }
            })->filter();
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
        $json = json_encode($data);

        Storage::disk('media')->put("ops-daily-report-{$date->toDateString()}.json", $json);

        //log into media table
        DB::table('media')->insert([
            'model_id'        => 1,
            'model_type'      => 'OpsDashboard Report',
            'collection_name' => "ops-daily-report-{$date->toDateString()}.json",
            'name'            => "ops-daily-report-{$date->toDateString()}.json",
            'file_name'       => "ops-daily-report-{$date->toDateString()}.json",
            'mime_type'       => 'application/json',
            'disk'            => 'media',
            'size'            => 1,
            'created_at'      => $date->toDateTimeString(),
            'updated_at'      => $date->toDateTimeString(),
        ]);


        if (app()->environment('worker')) {
            sendSlackMessage('#callcenter_ops',
                "Daily Call Center Operations Report for {$date->toDateString()} created. \n");
        }
    }


    public function calculateDailyTotalRow($rows)
    {

        foreach ($rows as $key => $value) {

            $totalCounts['ccmCounts']['zero'][]                   = $value['ccmCounts']['zero'];
            $totalCounts['ccmCounts']['0to5'][]                   = $value['ccmCounts']['0to5'];
            $totalCounts['ccmCounts']['5to10'][]                  = $value['ccmCounts']['5to10'];
            $totalCounts['ccmCounts']['10to15'][]                 = $value['ccmCounts']['10to15'];
            $totalCounts['ccmCounts']['15to20'][]                 = $value['ccmCounts']['15to20'];
            $totalCounts['ccmCounts']['20plus'][]                 = $value['ccmCounts']['20plus'];
            $totalCounts['ccmCounts']['total'][]                  = $value['ccmCounts']['total'];
            $totalCounts['ccmCounts']['priorDayTotals'][]         = $value['ccmCounts']['priorDayTotals'];
            $totalCounts['countsByStatus']['enrolled'][]          = $value['countsByStatus']['enrolled'];
            $totalCounts['countsByStatus']['pausedPatients'][]    = $value['countsByStatus']['pausedPatients'];
            $totalCounts['countsByStatus']['withdrawnPatients'][] = $value['countsByStatus']['withdrawnPatients'];
            $totalCounts['countsByStatus']['delta'][]             = $value['countsByStatus']['delta'];
            $totalCounts['countsByStatus']['gCodeHold'][]         = $value['countsByStatus']['gCodeHold'];


        }

        $totalRow['ccmCounts']['zero']                   = array_sum($totalCounts['ccmCounts']['zero']);
        $totalRow['ccmCounts']['0to5']                   = array_sum($totalCounts['ccmCounts']['0to5']);
        $totalRow['ccmCounts']['5to10']                  = array_sum($totalCounts['ccmCounts']['5to10']);
        $totalRow['ccmCounts']['10to15']                 = array_sum($totalCounts['ccmCounts']['10to15']);
        $totalRow['ccmCounts']['15to20']                 = array_sum($totalCounts['ccmCounts']['15to20']);
        $totalRow['ccmCounts']['20plus']                 = array_sum($totalCounts['ccmCounts']['20plus']);
        $totalRow['ccmCounts']['total']                  = array_sum($totalCounts['ccmCounts']['total']);
        $totalRow['ccmCounts']['priorDayTotals']         = array_sum($totalCounts['ccmCounts']['priorDayTotals']);
        $totalRow['countsByStatus']['enrolled']          = array_sum($totalCounts['countsByStatus']['enrolled']);
        $totalRow['countsByStatus']['pausedPatients']    = array_sum($totalCounts['countsByStatus']['pausedPatients']);
        $totalRow['countsByStatus']['withdrawnPatients'] = array_sum($totalCounts['countsByStatus']['withdrawnPatients']);
        $totalRow['countsByStatus']['delta']             = array_sum($totalCounts['countsByStatus']['delta']);
        $totalRow['countsByStatus']['gCodeHold']         = array_sum($totalCounts['countsByStatus']['gCodeHold']);

        return collect($totalRow);

    }
}

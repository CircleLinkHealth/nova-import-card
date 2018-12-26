<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Practice;
use App\Repositories\Cache\UserNotificationList;
use App\Repositories\Cache\View;
use App\Repositories\OpsDashboardPatientEloquentRepository;
use App\Services\OpsDashboardService;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class GenerateOpsDashboardCSVReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $service;
    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user    = $user;
        $this->service = new OpsDashboardService(new OpsDashboardPatientEloquentRepository());
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
        $date = Carbon::now();

        ini_set('memory_limit', '512M');

        $practices = Practice::select(['id', 'display_name'])
            ->activeBillable()
            ->with([
                'patients' => function ($p) use ($date) {
                    $p->with([
                        'patientSummaries' => function ($s) use ($date) {
                            $s->where('month_year', $date->copy()->startOfMonth());
                        },
                        'patientInfo.revisionHistory' => function ($r) use ($date) {
                            $r->where('key', 'ccm_status')
                                ->where(
                                  'created_at',
                                  '>=',
                                  $date->copy()->subDay()->setTimeFromTimeString('23:30')
                              );
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
            if (null != $row) {
                $rows[$practice->display_name] = $row;
            }
        }
        $rows['CircleLink Total'] = $this->calculateDailyTotalRow($rows);
        $rows                     = collect($rows);

        $fileName = "CLH-Ops-CSV-Report-{$date->format('Y-m-d-H:i:s')}";

        $excel = Excel::create($fileName, function ($excel) use (
            $rows,
            $hoursBehind,
            $date
        ) {
            // Set the title
            $excel->setTitle('CLH Ops Daily Report');

            // Chain the setters
            $excel->setCreator('CLH System')
                ->setCompany('CircleLink Health');

            // Call them separately
            $excel->setDescription('CLH Ops Daily Report');

            // Our first sheet
            $excel->sheet('Sheet 1', function ($sheet) use (
                $rows,
                $hoursBehind,
                $date
            ) {
                $sheet->cell('A1', function ($cell) use ($date) {
                    // manipulate the cell
                    $cell->setValue("Ops Report from: {$date->copy()->subDay()->setTimeFromTimeString('23:30')->toDateTimeString()} to: {$date->toDateTimeString()}");
                });
                $sheet->cell('A2', function ($cell) use ($hoursBehind) {
                    // manipulate the cell
                    $cell->setValue("HoursBehind: {$hoursBehind}");
                });

                $sheet->appendRow([
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
                foreach ($rows as $key => $value) {
                    $sheet->appendRow([
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
                    ]);
                }
            });
        });

        $report = $excel->store('xls', false, true);

        $x = $this->user
            ->saasAccount
            ->addMedia($report['full'])
            ->toMediaCollection("CLH-Ops-CSV-Reports-{$date->toDateString()}");

        //hack
//        $json = [
//          'media_id' => $x->id,
//        ];
//        $str = json_encode($json);
//        $str2 = base64_encode($str);

        $file['name']       = "{$fileName}.xls";
        $file['collection'] = "CLH-Ops-CSV-Reports-{$date->toDateString()}";

        $viewHashKey = (new View())->storeViewInCache('admin.opsDashboard.csv', [
            'file' => $file,
            'date' => $date,
        ]);

        $userNotification = new UserNotificationList($this->user);

        $userNotification->push(
            'Ops Dashboard CSV report',
            "Ops Dashboard CSV report for {$date->toDateTimeString()}",
            linkToCachedView($viewHashKey),
            'Go to page'
        );
    }
}

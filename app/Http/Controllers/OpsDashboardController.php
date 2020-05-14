<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Charts\OpsChart;
use App\Repositories\OpsDashboardPatientEloquentRepository;
use App\Services\OpsDashboardService;
use Carbon\Carbon;
use CircleLinkHealth\Core\Exports\FromArray;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class OpsDashboardController extends Controller
{
    private $repo;
    private $service;

    /**
     * OpsDashboardController constructor.
     */
    public function __construct(
        OpsDashboardService $service,
        OpsDashboardPatientEloquentRepository $repo
    ) {
        $this->service = $service;
        $this->repo    = $repo;
    }

    public function calculateBillingChurnTotalRow($rows, $months)
    {
        $total['Billed']            = [];
        $total['Added to Billing']  = [];
        $total['Lost from Billing'] = [];

        foreach ($rows as $practice => $patients) {
            foreach ($patients['Billed'] as $month => $count) {
                $total['Billed'][$month][] = $count;
            }

            foreach ($patients['Added to Billing'] as $month => $count) {
                $total['Added to Billing'][$month][] = $count;
            }

            foreach ($patients['Lost from Billing'] as $month => $count) {
                $total['Lost from Billing'][$month][] = $count;
            }
        }

        foreach ($months as $month) {
            $totalRow['Billed'][$month->format('m, Y')]            = array_sum($total['Billed'][$month->format('m, Y')]);
            $totalRow['Added to Billing'][$month->format('m, Y')]  = array_sum($total['Added to Billing'][$month->format('m, Y')]);
            $totalRow['Lost from Billing'][$month->format('m, Y')] = array_sum($total['Lost from Billing'][$month->format('m, Y')]);
        }

        return collect($totalRow);
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

    public function calculateLostAddedRow($rows)
    {
        $total    = [];
        $totalRow = [];
        foreach ($rows as $key => $value) {
            $total['Added'][]     = $value['Added'];
            $total['Paused'][]    = $value['Paused'];
            $total['Withdrawn'][] = $value['Withdrawn'];
            $total['Delta'][]     = $value['Delta'];
        }

        $totalRow['Added']     = array_sum($total['Added']);
        $totalRow['Paused']    = array_sum($total['Paused']);
        $totalRow['Withdrawn'] = array_sum($total['Withdrawn']);
        $totalRow['Delta']     = array_sum($total['Delta']);

        return collect($totalRow);
    }

    public function dailyCsv(Request $request)
    {
        if ($request->has('date')) {
            $requestDate = Carbon::parse($request['date']);
            $date        = $requestDate->copy();
        } else {
            //if the admin loads the page today, we need to display last night's report
            $date = Carbon::yesterday();
        }
        //there are no compatible reports in the cloud before this day
        $noReportDates = Carbon::parse('5 August 2018');
        //for older reports that dont have dateGenerated
        $dateGenerated = null;

        $json = optional(SaasAccount::whereSlug('circlelink-health')
            ->first()
            ->getMedia("ops-daily-report-{$date->toDateString()}.json")
            ->sortByDesc('id')
            ->first())
            ->getFile();

        //first check if we have a valid file
        if ( ! $json || $date <= $noReportDates) {
            $hoursBehind = 'N/A';
            $rows        = null;
        } else {
            //then check if it's in json format
            if ( ! is_json($json)) {
                throw new \Exception('File retrieved is not in json format.', 500);
            }

            $data        = json_decode($json, true);
            $hoursBehind = $data['hoursBehind'];
            $rows        = $data['rows'];
            if (array_key_exists('dateGenerated', $data)) {
                $dateGenerated = Carbon::parse($data['dateGenerated']);
            }
        }

        $fileName = "CLH-Ops-CSV-Report-{$date->format('Y-m-d-H:i:s')}.xls";

        $reportRows = collect();

        $reportRows->push(["Ops Report for: {$date->copy()->toDateString()}"]);
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

        foreach ($rows as $key => $value) {
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

        return (new FromArray($fileName, $reportRows->all(), []))->download($fileName);
    }

    public function downloadCsvReport($fileName, $collection)
    {
        $csv = auth()->user()
            ->saasAccount
            ->getMedia($collection)
            ->where('file_name', $fileName)
            ->first();

        return $this->downloadMedia($csv);
    }

    public function getBillingChurn(Request $request)
    {
        //Page times out. We should implement solution similar to OpsDashbboard (queue job save data on S3), or SQL view, or Nova page with metrics
        return 'Page is unavailable. To be fixed in CPM-1717';
        if ($request->has('months')) {
            $months = $request['months'];
            if ('all' == $months) {
                $months = 8;
            }
        } else {
            $months = 6;
        }

        $date     = Carbon::today();
        $fromDate = $date->copy()->subMonth($months)->startOfMonth()->startOfDay();
        $months   = $this->getMonths($date, $months);

        $practices = Practice::activeBillable()
            ->with([
                'patients' => function ($u) use ($fromDate) {
                    $u->with([
                        'patientSummaries' => function ($s) use ($fromDate) {
                            $s->whereNotNull('actor_id')
                                ->where('approved', 1)
                                ->where('month_year', '>=', $fromDate->toDateString());
                        },
                    ]);
                },
            ])->get()
            ->sortBy('display_name');

        foreach ($practices as $practice) {
            $summaries = $practice->patients->map(function ($p) {
                return $p->patientSummaries;
            })->filter()->flatten();
            $rows[$practice->display_name] = $this->service->billingChurnRow($summaries, $months);
        }
        $total = $this->calculateBillingChurnTotalRow($rows, $months);
        $rows  = collect($rows);

        return view('admin.opsDashboard.billing-churn', compact([
            'date',
            'fromDate',
            'rows',
            'months',
            'total',
        ]));
    }

    public function getLostAdded(Request $request)
    {
        return 'This page is under construction.';
        $today   = Carbon::today();
        $maxDate = $today->copy()->subDay(1);

        if ($request['fromDate'] && $request['toDate']) {
            $fromDate = $request['fromDate'];
            $toDate   = $request['toDate'];
        } else {
            $toDate   = $today->copy()->subDay(1)->setTimeFromTimeString('23:00');
            $fromDate = $toDate->copy()->subDay(1);
        }

        $practices = Practice::activeBillable()
            ->with([
                'patients' => function ($p) use ($fromDate) {
                    $p->with([
                        'activities' => function ($a) use ($fromDate) {
                            $a->where(
                                'performed_at',
                                '>=',
                                $fromDate->copy()->startOfMonth()->startOfDay()
                            );
                        },
                        'revisionHistory' => function ($r) use ($fromDate) {
                            $r->where('key', 'ccm_status')
                                ->where('created_at', '>=', $fromDate->copy()->startOfDay());
                        },
                        'patientInfo',
                    ]);
                },
            ])
            ->whereHas('patients.patientInfo')
            ->get()
            ->sortBy('display_name');

        $rows = [];
        foreach ($practices as $practice) {
            $patients = $practice->patients->where('program_id', $practice->id);
            $row      = $this->service->lostAddedRow($patients, $fromDate);
            if (null != $row) {
                $rows[$practice->display_name] = $row;
            }
        }

        $rows['Total'] = $this->calculateLostAddedRow($rows);
        $rows          = collect($rows);

        return view('admin.opsDashboard.lost-added', compact([
            'fromDate',
            'toDate',
            'maxDate',
            'rows',
        ]));
    }

    public function getMonths(Carbon $date, $number)
    {
        $months = [];

        for ($x = $number; $x > 0; --$x) {
            $months[] = $date->copy()->subMonth($x)->startOfMonth();
        }

        return collect($months);
    }

    public function getPatientList(Request $request)
    {
        $today   = Carbon::today();
        $maxDate = $today->copy()->subDay(1);

        $requestToDate = new Carbon($request['toDate']);
        $toDate        = $requestToDate->copy()->setTimeFromTimeString('23:00');
        $fromDate      = new Carbon($request['fromDate']);

        $status     = $request['status'];
        $practiceId = $request['practice_id'];

        $practices = Practice::activeBillable()->get()->sortBy('display_name');

        $patients = $this->repo->getPatientsByStatus(
            $fromDate->startOfDay()->toDateTimeString(),
            $toDate->endOfDay()->toDateTimeString()
        );

        $patients = $patients->whereIn('program_id', $practices->pluck('id')->all());

        if ('all' != $practiceId) {
            $patients = $this->service->filterPatientsByPractice($patients, $practiceId);
        }
        if ('all' !== $status) {
            $patients = $this->service->filterPatientsByStatus($patients, $status);
        }

        $patients = $this->paginatePatients($patients);
        $patients = $patients->withPath('admin/reports/ops-dashboard/patient-list');

        return view('admin.opsDashboard.patient-list', compact([
            'patients',
            'practices',
            'fromDate',
            'toDate',
            'maxDate',
            'status',
            'practiceId',
        ]));
    }

    public function getPatientListIndex()
    {
        return 'This page is under construction';
        $today   = Carbon::today();
        $maxDate = $today->copy()->subDay(1);

        $toDate     = $today->copy()->subDay(1)->setTimeFromTimeString('23:00');
        $fromDate   = $toDate->copy()->subDay(1);
        $status     = 'all';
        $practiceId = 'all';

        $practices = Practice::activeBillable()->get()->sortBy('display_name');

        $patients = $this->repo->getPatientsByStatus($fromDate->toDateTimeString(), $toDate->toDateTimeString());

        $patients = $patients->whereIn('program_id', $practices->pluck('id')->all());

        $patients = $this->paginatePatients($patients);
        $patients = $patients->withPath('admin/reports/ops-dashboard/patient-list-index');

        return view('admin.opsDashboard.patient-list', compact([
            'patients',
            'practices',
            'fromDate',
            'toDate',
            'maxDate',
            'status',
            'practiceId',
        ]));
    }

    /**
     * Gets Patient Counts for table: CarePlan Manager Patient Totals,
     * for today, for specific day.
     *
     * @throws \Exception
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $maxDate = Carbon::today()->subDay(1);

        if ($request->has('date')) {
            $requestDate = new Carbon($request['date']);
            $date        = $requestDate->copy();
        } else {
            //if the admin loads the page today, we need to display last night's report
            $date = $maxDate->copy();
        }
        //there are no compatible reports in the cloud before this day
        $noReportDates = Carbon::parse('5 August 2018');
        //for older reports that dont have dateGenerated
        $dateGenerated = null;

        $json = optional(SaasAccount::whereSlug('circlelink-health')
            ->first()
            ->getMedia("ops-daily-report-{$date->toDateString()}.json")
            ->sortByDesc('id')
            ->first())
            ->getFile();

        //first check if we have a valid file
        if ( ! $json || $date <= $noReportDates) {
            $hoursBehind = 'N/A';
            $rows        = null;
        } else {
            //then check if it's in json format
            if ( ! is_json($json)) {
                throw new \Exception('File retrieved is not in json format.', 500);
            }

            $data        = json_decode($json, true);
            $hoursBehind = $data['hoursBehind'];
            $rows        = $data['rows'];
            if (array_key_exists('dateGenerated', $data)) {
                $dateGenerated = Carbon::parse($data['dateGenerated']);
            }
        }

        return view('admin.opsDashboard.daily', compact([
            'date',
            'maxDate',
            'hoursBehind',
            'rows',
            'dateGenerated',
        ]));
    }

    public function makeExcelPatientReport(Request $request)
    {
        $toDate     = new Carbon($request['toDate']);
        $fromDate   = new Carbon($request['fromDate']);
        $status     = $request['status'];
        $practiceId = $request['practice_id'];

        $report = $this->service->getExcelReport($fromDate, $toDate, $status, $practiceId);

        return $this->downloadMedia($report);
    }

    public function opsGraph()
    {
        return view('charts.ops')->with('chart', OpsChart::clhGrowthChart());
    }

    private function paginatePatients($patients)
    {
        $currentPage              = LengthAwarePaginator::resolveCurrentPage();
        $perPage                  = 20;
        $currentPageSearchResults = $patients->slice(($currentPage - 1) * $perPage, $perPage)->all();

        return new LengthAwarePaginator($currentPageSearchResults, count($patients), $perPage);
    }
}

<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateOpsDashboardCSVReport;
use App\Practice;
use App\Repositories\OpsDashboardPatientEloquentRepository;
use App\SaasAccount;
use App\Services\OpsDashboardService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class OpsDashboardController extends Controller
{

    private $service;
    private $repo;

    /**
     * OpsDashboardController constructor.
     *
     * @param OpsDashboardService $service
     * @param OpsDashboardPatientEloquentRepository $repo
     */
    public function __construct(
        OpsDashboardService $service,
        OpsDashboardPatientEloquentRepository $repo
    ) {
        $this->service = $service;
        $this->repo    = $repo;
    }


    /**
     * Gets Patient Counts for table: CarePlan Manager Patient Totals,
     * for today, for specific day.
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
                throw new \Exception("File retrieved is not in json format.", 500);
            }

            $data        = json_decode($json, true);
            $hoursBehind = $data['hoursBehind'];
            $rows        = $data['rows'];
        }

        return view('admin.opsDashboard.daily', compact([
            'date',
            'maxDate',
            'hoursBehind',
            'rows',
        ]));
    }

    public function dailyCsv()
    {

        GenerateOpsDashboardCSVReport::dispatch(auth()->user())->onQueue('high');

        return "Waldo is working on compiling the reports you requested. <br> Give it a minute, and then head to " . link_to('/jobs/completed') . " and refresh frantically to see a link to the report you requested.";

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

    public function getLostAdded(Request $request)
    {
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
                                         'activities'      => function ($a) use ($fromDate) {
                                             $a->where('performed_at', '>=',
                                                 $fromDate->copy()->startOfMonth()->startOfDay());
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
            if ($row != null) {
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

    public function getPatientListIndex()
    {

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
        $patients = $patients->withPath("admin/reports/ops-dashboard/patient-list-index");


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


        $patients = $this->repo->getPatientsByStatus($fromDate->startOfDay()->toDateTimeString(),
            $toDate->endOfDay()->toDateTimeString());

        $patients = $patients->whereIn('program_id', $practices->pluck('id')->all());

        if ($practiceId != 'all') {
            $patients = $this->service->filterPatientsByPractice($patients, $practiceId);
        }
        if ($status !== 'all') {
            $patients = $this->service->filterPatientsByStatus($patients, $status);
        }

        $patients = $this->paginatePatients($patients);
        $patients = $patients->withPath("admin/reports/ops-dashboard/patient-list");


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

    public function getBillingChurn(Request $request)
    {
        if ($request->has('months')) {
            $months = $request['months'];
            if ($months == 'all') {
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
                                             $s->where('actor_id', '!=', null)
                                               ->where('approved', 1)
                                               ->where('month_year', '>=', $fromDate->toDateString());
                                         },
                                     ]);
                                 },
                             ])->get()
                             ->sortBy('display_name');


        foreach ($practices as $practice) {


            $summaries                     = $practice->patients->map(function ($p) {
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

    /**
     * Old dashboard
     *
     * Gets Patient Counts for table: CarePlan Manager Patient Totals
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getTotalPatientData(Request $request)
    {
        $practice           = false;
        $patientsByPractice = null;

        $date     = Carbon::createFromFormat('Y-m-d', $request['date']);
        $dateType = $request['type'];

        $practices = Practice::active()->get();

        $totals = $this->service->getCpmPatientTotals($date, $dateType);

        return view('admin.opsDashboard.index', compact([
            'practices',
            'totals',
            'patientsByPractice',
            'practice',
            'date',
            'dateType',

        ]));

    }

    /**
     * Old dashboard
     *
     * Gets Patient Counts for table: Patient stats by Practice.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getPatientsByPractice(Request $request)
    {

        $totals = null;

        $date     = new Carbon($request['date']);
        $dateType = $request['type'];
        $practice = Practice::find($request['practice_id']);


        $practices          = Practice::active()->get();
        $patientsByPractice = $this->service->getCpmPatientTotals($date, $dateType, $practice->id);


        return view('admin.opsDashboard.index', compact([
            'practices',
            'totals',
            'patientsByPractice',
            'practice',
            'date',
            'dateType',
        ]));

    }


    /**
     * Old dashboard
     *
     * Gets Patient list for Total Patients Table, and Patients by Practice Table.
     *
     *
     * @param Request $request
     *
     * @param $type (type of the column (day, week, month, total)
     * @param $date
     * @param $dateType (type of date for the query (specific day, week, or month)
     * @param null $practiceId
     *
     * @return mixed
     */
    public function getList(Request $request, $type, $date, $dateType, $practiceId = null)
    {

        $to     = null;
        $date   = new Carbon($date);
        $toDate = null;


        if ($type == 'day') {
            if ($dateType == 'day') {
                $dayFromDate = $date->copy()->startOfDay()->toDateTimeString();
                $dayToDate   = $date->copy()->endOfDay()->toDateTimeString();
                $patients    = $this->service->getTotalPatients($dayFromDate, $dayToDate);
            }
            if ($dateType == 'week') {
                $dayFromDate = $date->copy()->endOfWeek()->startOfDay()->toDateTimeString();
                $dayToDate   = $date->copy()->endOfWeek()->endOfDay()->toDateTimeString();
                $patients    = $this->service->getTotalPatients($dayFromDate, $dayToDate);
            }
            if ($dateType == 'month') {
                $dayFromDate = $date->copy()->endOfMonth()->startOfDay()->toDateTimeString();
                $dayToDate   = $date->copy()->endOfMonth()->endOfDay()->toDateTimeString();
                $patients    = $this->service->getTotalPatients($dayFromDate, $dayToDate);
            }
        }
        if ($type == 'week') {
            if ($dateType == 'day' || 'week') {
                $fromDate = $date->copy()->startOfWeek()->startOfDay()->toDateTimeString();
                $toDate   = $date->copy()->endOfWeek()->endOfDay()->toDateTimeString();
                $patients = $this->service->getTotalPatients($fromDate, $toDate);
            }
            if ($dateType == 'month') {
                $fromDate = $date->copy()->endOfMonth()->startOfWeek()->startOfDay()->toDateTimeString();
                $toDate   = $date->copy()->endOfMonth()->endOfDay()->toDateTimeString();
                $patients = $this->service->getTotalPatients($fromDate, $toDate);
            }
        }
        if ($type == 'month') {
            $fromDate = $date->copy()->startOfMonth()->startOfDay()->toDateTimeString();
            $toDate   = $date->copy()->endOfMonth()->endOfDay()->toDateTimeString();
            $patients = $this->service->getTotalPatients($fromDate, $toDate);
        }
        if ($type == 'total') {
            $patients = $this->service->getTotalPatients();
        }

        $practice = null;
        if ($practiceId) {
            $practice = Practice::find($practiceId);
            $patients = $this->service->filterPatientsByPractice($patients, $practiceId);
            $patients = new Collection($patients);
        }


        $patients = $this->paginatePatients($patients);
        $patients = $patients->withPath("admin/reports/ops-dashboard/patient-list/$type/$date/$dateType/$practiceId");


        return view('admin.opsDashboard.list', compact([
            'patients',
            'type',
            'date',
            'practice',
            'to',
        ]));

    }


    /**
     *
     * Old dashboard
     * Gets Paused Patients List for two specific dates.
     *
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getPausedPatientList(Request $request)
    {

        $practice = null;
        $date     = new Carbon($request['fromDate']);
        $to       = new Carbon($request['toDate']);

        $fromDate = $date->startOfDay()->toDateTimeString();
        $toDate   = $to->endOfDay()->toDateTimeString();

        $patients = $this->service->getPausedPatients($fromDate, $toDate);


        $patients = $this->paginatePatients($patients);
        $patients = $patients->withPath("admin/reports/ops-dashboard/paused-patient-list");

        return view('admin.opsDashboard.list', compact([
            'patients',
            'practice',
            'date',
            'to',
        ]));
    }

    private function paginatePatients($patients)
    {

        $currentPage              = LengthAwarePaginator::resolveCurrentPage();
        $perPage                  = 20;
        $currentPageSearchResults = $patients->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $patients                 = new LengthAwarePaginator($currentPageSearchResults, count($patients), $perPage);

        return $patients;

    }

    public function getMonths(Carbon $date, $number)
    {

        $months = [];

        for ($x = $number; $x > 0; $x--) {

            $months[] = $date->copy()->subMonth($x)->startOfMonth();

        }

        return collect($months);
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


}

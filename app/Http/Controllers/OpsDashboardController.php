<?php

namespace App\Http\Controllers;

use App\Practice;
use App\Repositories\OpsDashboardPatientEloquentRepository;
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
        $this->repo = $repo;
    }


    /**
     * Gets Patient Counts for table: CarePlan Manager Patient Totals,
     * for today, for specific day.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $date     = Carbon::today();
        $date = $date->copy()->subDay(1)->setTimeFromTimeString('23:00');

        $hoursBehind = $this->service->calculateHoursBehind($date->toDateTimeString());
        $practices = Practice::active()->get();
        $rows = [];
        foreach ($practices as $practice){
            $rows[$practice->display_name]= $this->service->dailyReportRow($practice, $date);
        }
        $rows = collect($rows);


        return view('admin.opsDashboard.daily', compact([
            'date',
            'hoursBehind',
            'rows',
//            'totalRow',
        ]));

    }

    public function dailyReport(Request $request)
    {
        $date     = new Carbon($request['date']);
        $date = $date->copy()->subDay(1)->setTimeFromTimeString('23:00');

        $hoursBehind = $this->service->calculateHoursBehind($date->toDateTimeString());
        $practices = Practice::active()->get();
        $rows = [];
        foreach ($practices as $practice){
            $rows[$practice->display_name]= $this->service->dailyReportRow($practice, $date);
        }
        $rows = collect($rows);


        return view('admin.opsDashboard.daily', compact([
            'date',
            'hoursBehind',
            'rows',
            //            'totalRow',
        ]));

    }

    public function getLostAddedIndex(){

        $date     = Carbon::today();
        $toDate = $date->copy()->subDay(1)->setTimeFromTimeString('23:00');
        $fromDate = $toDate->copy()->subDay(1);


        $rows = [];
        $practices = Practice::active()->get();
        foreach($practices as $practice){
            $rows[$practice->display_name]= $this->service->lostAddedRow($practice, $fromDate->toDateTimeString(), $toDate->toDateTimeString());
        }


        $rows = collect($rows);
        $total = 0;

        return view('admin.opsDashboard.lost-added', compact([
            'fromDate',
            'toDate',
            'rows',
            //            'totalRow',
        ]));

    }

    public function getPatientListIndex(){

        $toDate = Carbon::today();
        $fromDate = $toDate->copy()->subYear(1);

        $patients = $this->repo->getPatientsByStatus($fromDate->toDateTimeString(), $toDate->toDateTimeString());


        $patients = $this->paginatePatients($patients);
        $patients = $patients->withPath("admin/reports/ops-dashboard/patient-list-index");




        return view('admin.opsDashboard.patient-list', compact([
            'patients',
            'fromDate',
            'toDate',
        ]));

    }


    /**
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

        $date = Carbon::createFromFormat('Y-m-d', $request['date']);
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


        $practices = Practice::active()->get();
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
                $dayFromDate  = $date->copy()->startOfDay()->toDateTimeString();
                $dayToDate  = $date->copy()->endOfDay()->toDateTimeString();
                $patients = $this->service->getTotalPatients($dayFromDate, $dayToDate);
            }
            if ($dateType == 'week') {
                $dayFromDate  = $date->copy()->endOfWeek()->startOfDay()->toDateTimeString();
                $dayToDate = $date->copy()->endOfWeek()->endOfDay()->toDateTimeString();
                $patients = $this->service->getTotalPatients($dayFromDate, $dayToDate);
            }
            if ($dateType == 'month') {
                $dayFromDate  = $date->copy()->endOfMonth()->startOfDay()->toDateTimeString();
                $dayToDate = $date->copy()->endOfMonth()->endOfDay()->toDateTimeString();
                $patients = $this->service->getTotalPatients($dayFromDate, $dayToDate);
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
     * Gets Paused Patients List for two specific dates.
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

    private function paginatePatients($patients){

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;
        $currentPageSearchResults = $patients->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $patients = new LengthAwarePaginator($currentPageSearchResults, count($patients), $perPage);

        return $patients;

    }

    public function getPatientNotesAndActivitiesPage(Request $request)
    {

    }


}

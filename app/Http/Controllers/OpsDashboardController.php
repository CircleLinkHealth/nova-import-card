<?php

namespace App\Http\Controllers;

use App\Patient;
use App\Practice;
use App\Repositories\OpsDashboardPatientEloquentRepository;
use App\Services\OpsDashboardService;
use App\User;
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
    public function index()
    {
        $today = Carbon::today();
        $maxDate = $today->copy()->subDay(1);

        //used by query to get patients with CCM time
        $date = $maxDate->copy()->setTimeFromTimeString('23:00');


        $enrolledPatients = User::ofType('participant')
                                ->with(['activities' => function ($activity) use ($date){
                                    $activity->where('performed_at', '>=', $date->copy()->startOfMonth()->startOfDay()->toDateTimeString())
                                             ->where('performed_at', '<=', $date->toDateTimeString());
    }])
                                ->whereHas('patientInfo', function ($patient) {
                $patient->where('ccm_status', Patient::ENROLLED);
            })->get();

        //used by query to get Patients by status
        $fromDate = $date->copy()->subDay();
        $patientsByStatus = $this->repo->getPatientsByStatus($fromDate->toDateTimeString(), $date->toDateTimeString());


        $hoursBehind = $this->service->calculateHoursBehind($date, $enrolledPatients);

        $allPractices = Practice::activeBillable()->get();

        $rows        = [];
        foreach ($allPractices as $practice) {
            $statusPatientsByPactice = $patientsByStatus->where('program_id', $practice->id);
            $patientsByPractice = $enrolledPatients->where('program_id', $practice->id);
            $row = $this->service->dailyReportRow($practice, $date, $patientsByPractice, $statusPatientsByPactice);
            if ($row != null){
                $rows[$practice->display_name] = $row;
            }
        }
        $rows['CircleLink Total'] = $this->calculateDailyTotalRow($rows);
        $rows                    = collect($rows);


        return view('admin.opsDashboard.daily', compact([
            'date',
            'maxDate',
            'hoursBehind',
            'rows',
        ]));

    }

    public function getDailyReport(Request $request)
    {
        $today = Carbon::today();
        $maxDate = $today->copy()->subDay(1);
        $requestDate = new Carbon($request['date']);
        $date = $requestDate->copy()->setTimeFromTimeString('23:00');

        $enrolledPatients = User::ofType('participant')
                                ->with(['activities' => function ($activity) use ($date){
                                    $activity->where('performed_at', '>=', $date->copy()->startOfMonth()->toDateTimeString())
                                             ->where('performed_at', '<=', $date->toDateTimeString());
                                }])
                                ->whereHas('patientInfo', function ($patient) {
                                    $patient->where('ccm_status', Patient::ENROLLED);
                                })->get();

        $fromDate = $date->copy()->subDay();

        $patientsByStatus = $this->repo->getPatientsByStatus($fromDate->copy()->toDateTimeString(), $date->toDateTimeString());


        $hoursBehind = $this->service->calculateHoursBehind($date, $enrolledPatients);

        $allPractices = Practice::activeBillable()->get();
        $rows        = [];
        foreach ($allPractices as $practice) {
            $statusPatientsByPractice = $patientsByStatus->where('program_id', $practice->id);
            $patientsByPractice = $enrolledPatients->where('program_id', $practice->id);
            $row = $this->service->dailyReportRow($practice, $date, $patientsByPractice, $statusPatientsByPractice);
            if ($row != null){
                $rows[$practice->display_name] = $row;
            }
        }

        $rows['CircleLink Total'] = $this->calculateDailyTotalRow($rows);
        $rows                    = collect($rows);


        return view('admin.opsDashboard.daily', compact([
            'date',
            'maxDate',
            'hoursBehind',
            'rows',
        ]));

    }

    public function getLostAddedIndex()
    {

        $today = Carbon::today();
        $maxDate = $today->copy()->subDay(1);

        $toDate   = $today->copy()->subDay(1)->setTimeFromTimeString('23:00');
        $fromDate = $toDate->copy()->subDay(1);

        $patientsByStatus = $this->repo->getPatientsByStatus($fromDate->copy()->toDateTimeString(), $toDate->toDateTimeString());

        $rows      = [];
        $allPractices = Practice::activeBillable()->get();
        foreach ($allPractices as $practice) {
            $statusPatientsByPractice = $patientsByStatus->where('program_id', $practice->id);
            $row = $this->service->lostAddedRow($statusPatientsByPractice);
            if ($row != null){
                $rows[$practice->display_name] = $row;
            }

        }

        $rows['Total'] = $this->calculateLostAddedRow($rows);
        $rows  = collect($rows);

        return view('admin.opsDashboard.lost-added', compact([
            'fromDate',
            'toDate',
            'maxDate',
            'rows',
        ]));

    }

    public function getLostAdded(Request $request)
    {
        $today = Carbon::today();
        $maxDate = $today->copy()->subDay(1);

        $requestToDate   = new Carbon($request['toDate']);
        $toDate = $requestToDate->copy()->setTimeFromTimeString('23:00');
        $fromDate = new Carbon($request['fromDate']);

        $patientsByStatus = $this->repo->getPatientsByStatus($fromDate->toDateTimeString(), $toDate->toDateTimeString());

        $rows      = [];
        $allPractices = Practice::activeBillable()->get();
        foreach ($allPractices as $practice) {
            $statusPatientsByPractice = $patientsByStatus->where('program_id', $practice->id);
            $row = $this->service->lostAddedRow($statusPatientsByPractice);
            if ($row != null){
                $rows[$practice->display_name] = $row;
            }

        }

        $rows['Total'] = $this->calculateLostAddedRow($rows);
        $rows  = collect($rows);


        return view('admin.opsDashboard.lost-added', compact([
            'fromDate',
            'toDate',
            'maxDate',
            'rows',
        ]));

    }

    public function getPatientListIndex()
    {

        $today = Carbon::today();
        $maxDate = $today->copy()->subDay(1);

        $toDate   = $today->copy()->subDay(1)->setTimeFromTimeString('23:00');
        $fromDate = $toDate->copy()->subDay(1);
        $status = 'all';
        $practiceId = 'all';
        $practices = Practice::activeBillable()->get();

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
        $today = Carbon::today();
        $maxDate = $today->copy()->subDay(1);

        $requestToDate   = new Carbon($request['toDate']);
        $toDate = $requestToDate->copy()->setTimeFromTimeString('23:00');
        $fromDate = new Carbon($request['fromDate']);


        $status   = $request['status'];
        $practiceId = $request['practice_id'];



        $practices = Practice::activeBillable()->get();

        $patients = $this->repo->getPatientsByStatus($fromDate->startOfDay()->toDateTimeString(),
            $toDate->endOfDay()->toDateTimeString());

        $patients = $patients->whereIn('program_id', $practices->pluck('id')->all());

        if ($practiceId != 'all'){
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


    public function makeExcelPatientReport(Request $request)
    {
        $toDate   = new Carbon($request['toDate']);
        $fromDate = new Carbon($request['fromDate']);
        $status   = $request['status'];
        $practiceId = $request['practice_id'];


        $report = $this->service->getExcelReport($fromDate, $toDate, $status, $practiceId);

            return $this->downloadMedia($report);
    }

    public function calculateLostAddedRow($rows)
    {
        $total['enrolled']          = [];
        $total['pausedPatients']    = [];
        $total['withdrawnPatients'] = [];
        $total['delta']             = [];

        foreach ($rows as $key => $value) {
            $total['enrolled'][]          = $value['enrolled'];
            $total['pausedPatients'][]    = $value['pausedPatients'];
            $total['withdrawnPatients'][] = $value['withdrawnPatients'];
            $total['delta'][]             = $value['delta'];
        }

        $totalRow['enrolled']          = array_sum($total['enrolled']);
        $totalRow['pausedPatients']    = array_sum($total['pausedPatients']);
        $totalRow['withdrawnPatients'] = array_sum($total['withdrawnPatients']);
        $totalRow['delta']             = array_sum($total['delta']);

        return collect($totalRow);

    }

    public function calculateDailyTotalRow($rows)
    {

        $totalCounts['ccmCounts']['zero']                   = [];
        $totalCounts['ccmCounts']['0to5']                   = [];
        $totalCounts['ccmCounts']['5to10']                  = [];
        $totalCounts['ccmCounts']['10to15']                 = [];
        $totalCounts['ccmCounts']['15to20']                 = [];
        $totalCounts['ccmCounts']['20plus']                 = [];
        $totalCounts['ccmCounts']['total']                  = [];
        $totalCounts['ccmCounts']['priorDayTotals']         = [];
        $totalCounts['countsByStatus']['enrolled']          = [];
        $totalCounts['countsByStatus']['pausedPatients']    = [];
        $totalCounts['countsByStatus']['withdrawnPatients'] = [];
        $totalCounts['countsByStatus']['delta']             = [];
        $totalCounts['countsByStatus']['gCodeHold']         = [];


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

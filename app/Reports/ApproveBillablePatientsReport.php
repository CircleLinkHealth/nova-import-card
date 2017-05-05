<?php namespace App\Reports;

use App\Activity;
use App\Call;
use App\CLH\CCD\Importer\SnomedToCpmIcdMap;
use App\CLH\CCD\Importer\SnomedToICD10Map;
use App\Models\CCD\Problem;
use App\Models\CPM\CpmInstruction;
use App\Models\CPM\CpmMisc;
use App\Models\CPM\CpmProblem;
use App\Patient;
use App\Practice;
use App\Role;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Yajra\Datatables\Facades\Datatables;

/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 1/6/17
 * Time: 10:10 AM
 */
//Supplies admin/reports/monthly-billing/v2/make

class ApproveBillablePatientsReport
{

    private $month;
    private $practice;
    private $patients;

    public function __construct(
        Carbon $month,
        $practice
    ) {

        $this->month = $month->firstOfMonth()->toDateString();
        $this->practice = $practice;

    }

    public function dataV1()
    {

        return $this->patients = User::with([
            'ccdProblems' => function ($query) {
                $query->whereNotNull('cpm_problem_id');
            },
            'cpmProblems',
            'patientInfo',
        ])
            ->whereHas('roles', function ($q) {
                $q->where('name', '=', 'participant');
            })
            ->where('program_id', '=', $this->practice)
            ->get();

    }

    public function dataV2()
    {

        $this->patients = Patient
            ::whereHas('patientSummaries', function ($q) {
                $q
//              ->where('month_year', Carbon::now()->firstOfMonth()->toDateString())
                    ->where('month_year', $this->month)
                    ->where('no_of_successful_calls', '>', 0);

            });

        if ($this->practice != 0) {

            $practice = $this->practice;

            $this->patients = $this->patients->whereHas('user', function ($k) use
            (
                $practice

            ) {
                $k->whereProgramId($practice);
            });
        }

        $this->patients = $this->patients->orderBy('updated_at', 'desc')
            ->get();

        return $this->patients;

    }

    public function format()
    {

        $count = 0;
        $formatted = [];

        foreach ($this->patients as $u) {

            $start = Carbon::parse($this->month)->startOfMonth()->startOfDay()->format("Y-m-d H:i:s");
            $end = Carbon::parse($this->month)->endOfMonth()->endOfDay()->format("Y-m-d H:i:s");

            $ccm = DB::table('lv_activities')
                ->where('patient_id', $u->id)
                ->whereBetween('performed_at', [
                    $start,
                    $end,
                ])
                ->sum('duration');

            if($ccm < 1200){
                continue;
            }

            $info = $u->patientInfo;

            if(is_null($info)){
                continue;
            }

            $report = $info->patientSummaries()
                ->where('month_year', $this->month)->first();

            if ($report == null) {
                continue;
            }

            //@todo add problem type and code
            $problems = $u->cpmProblems()->take(2)->get();
            $reportId = $report->id;

            $billableProblems = [];

            $lacksProblems = false; // ;)
            $lacksCode = false; // ;)

            //for JS problem picker
            $options = $u->ccdProblems()->pluck('name');
            $options = implode('|', $options->toArray());

            //First look for problems in the report itself. If no problems, then find problems from CCM. If none, give select box
            for ($i = 0; $i < 2; $i++) {

                $problemName = 'billable_problem' . ($i + 1);
                $problemCode = 'billable_problem' . ($i + 1) . '_code';

                if ($report->$problemName == '') {

                    if (isset($problems[$i])) {

                        $report->$problemName = $problems[$i]->name;
                        $billableProblems[$i]['name'] = $report->$problemName;

                        $code = SnomedToCpmIcdMap::whereCpmProblemId($problems[$i]->id)->first()->icd_10_code;

                        if($report->$problemCode = ''){

                            $report->$problemCode = $code;

                        } else {

                            $lacksCode = true;
                            $report->$problemCode = "<button style='font-size: 10px' class='btn btn-primary codePicker' patient='$u->fullName' name=$problemCode value='$options' id='$report->id'>Select Code</button >";

                        }

                        $billableProblems[$i]['code'] = $report->$problemCode;

                    } else {

                        $name = 'billable_problem' . ($i + 1);

                        $lacksProblems = true;
                        $lacksCode = true;

                        $billableProblems[$i]['name'] = "<button style='font-size: 10px' class='btn btn-primary problemPicker' patient='$u->fullName' name=$name value='$options' id='$report->id'>Select Problem</button >";
                        $billableProblems[$i]['code'] = "<button style='font-size: 10px' class='btn btn-primary codePicker' patient='$u->fullName' name=$name value='$options' id='$report->id'>Select Code</button >";

                    }

                } else { // there's a problem

                    //if there is a problem but no code

                    if ($report->$problemCode == ''){

                        $problem = $report->$problemName;

                        $name = 'billable_problem' . ($i + 1);

                        $lacksCode = true;

                        $billableProblems[$i]['code'] = "<button style='font-size: 10px' class='btn btn-primary problemPicker' patient='$u->fullName' name=$name value='$problem' id='$report->id'>Select Code</button >";

                    } else {

                        $billableProblems[$i]['code'] = $report->$problemCode;

                    }

                    $billableProblems[$i]['name'] = $report->$problemName;

                }

            }

            $report->save();

            //if patient was paused/withdrawn and acted upon already, it's not QA no more
            $isNotEnrolledAndApproved = ($report->actor_id == null) && ($info->ccm_status == 'withdrawn' || $info->ccm_status == 'paused');

            if ($lacksProblems || $report->rejected == 1 || $lacksCode) {

                $approved = '';

            } else {

                $approved = 'checked';

            }

            $rejected = ($report->rejected == 1)
                ? 'checked'
                : '';

            $report->approved = ($approved == '')
                ? 0
                : 1;

            $toQA = 0;
            if ($approved == '' && $rejected == '') {
                $toQA = 1;
            }

            $report->save();

            $name = "<a href = " . URL::route('patient.careplan.show', [
                    'patient' => $u->id,
                    'page'    => 1,
                ]) . "  target='_blank' >" . $u->fullName . "</a>";

            $formatted[$count] = [

                'name'                   => $name,
                'provider'               => $u->billingProvider()->fullName,
                'practice'               => $u->primaryPractice->display_name,
                'dob'                    => $info->birth_date,
                'ccm'                    => round($ccm / 60, 2),
                'problem1'               => $billableProblems[0]['name'],
                'problem1_code'          => $billableProblems[0]['code'],
                'problem2'               => $billableProblems[1]['name'],
                'problem2_code'          => $billableProblems[1]['code'],
                'no_of_successful_calls' => $report->no_of_successful_calls,
                'status'                 => $info->ccm_status,
                'approve'                => "<input type = \"checkbox\" class='approved_checkbox' id='$reportId' $approved>",
                'reject'                 => "<input type=\"checkbox\" class='rejected_checkbox' id='$reportId' $rejected>",
                //used to reference cells for jQuery ops
                'report_id'              => $reportId ?? null,
                //this is a hidden sorter
                'qa'                     => $toQA,
                'problems'               => $options,
                'lacksProblems'          => $lacksProblems || $lacksCode

            ];

            $count++;

        }

        return Datatables::of(collect($formatted))
            ->addColumn('background_color', function ($a) {
                if ($a['lacksProblems'] || $a['status'] == 'withdrawn' || $a['status'] == 'paused') {
                    return 'rgba(255, 252, 96, 0.407843)';
                } else {
                    return '';
                }
            })
            ->make(true);

    }


}
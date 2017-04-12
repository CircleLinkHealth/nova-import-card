<?php namespace App\Reports;

use App\Patient;
use App\User;
use Carbon\Carbon;
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

    private $data;
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

    public function data()
    {

        $this->patients = Patient
            ::whereHas('patientSummaries', function ($q) {
                $q->where('ccm_time', '>', 1199)
//                    ->where('month_year', Carbon::now()->firstOfMonth()->toDateString())
                    ->where('month_year', '2017-03-01')
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
            ->pluck('user_id');

        return $this->patients;

    }

    public function format()
    {

        $count = 0;
        $formatted = [];

        foreach ($this->patients as $p) {

            $u = User::find($p);
            $info = $u->patientInfo;

            $report = $info->patientSummaries()
//                    ->where('month_year', Carbon::now()->firstOfMonth()->toDateString());
                ->where('month_year', '2017-03-01')->first();

            if ($report == null) {
                continue;
            }

            //@todo add problem type and code
            $problems = $u->cpmProblems()->take(2)->pluck('name');
            $reportId = $report->id;

            $lacksProblems = false; // ;)

            //for JS problem picker
            $options = $u->ccdProblems()->pluck('name');
            $options = implode('|', $options->toArray());

            //First look for problems in the report itself. If no problems, then find problems from CCM. If none, give select box

            //Handle problem col 1
            if ($report->billable_problem1 == '') {

                if (!isset($problems[0])) {

                    $lacksProblems = true;

                    $problems[0] = "<button style='font-size: 10px' class='btn btn-primary problemPicker' name='billable_problem1' value='$options' id='$report->id'>Select Eligible Problem</button >";

                } else {

                    //if isset, then mark on report.
                    $report->billable_problem1 = $problems[0];

                }

            } else {

                $problems[0] = $report->billable_problem1;

            }

            //Handle problem col 2
            if ($report->billable_problem2 == '') {

                if (!isset($problems[1])) {

                    $lacksProblems = true;

                    $problems[1] = "<button style='font-size: 10px' class='btn btn-primary problemPicker' name='billable_problem2' value='$options' id='$report->id'>Select Eligible Problem</button >";

                } else {

                    //if isset, then mark on report.
                    $report->billable_problem2 = $problems[1];

                }

            } else {

                $problems[1] = $report->billable_problem2;

            }

            //if patient was paused/withdrawn and acted upon already, it's not QA no more
            $isNotEnrolledAndApproved = ($report->actor_id == null) && ($info->ccm_status == 'withdrawn' || $info->ccm_status == 'paused');

            if ($lacksProblems || $report->rejected == 1) {

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
                ]) . " > " . $u->fullName . "</a > ";

            $formatted[$count] = [

                'name'                   => $name,
                'provider'               => $u->billingProvider()->fullName,
                'practice'               => $u->primaryPractice->display_name,
                'dob'                    => $info->birth_date,
                'ccm'                    => round($report->ccm_time / 60, 2),
                'problem1'               => $problems[0],
                'problem2'               => $problems[1],
                'no_of_successful_calls' => $report->no_of_successful_calls,
                'status'                 => $info->ccm_status,
                'approve'                => "<input type = \"checkbox\" class='approved_checkbox' id='$reportId' $approved>",
                'reject'                 => "<input type=\"checkbox\" class='rejected_checkbox' id='$reportId' $rejected>",
                //used to reference cells for jQuery ops
                'report_id'              => $reportId ?? null,
                //this is a hidden sorter
                'qa'                     => $toQA,
                'problems'               => $options,
                'lacksProblems'          => $lacksProblems,

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
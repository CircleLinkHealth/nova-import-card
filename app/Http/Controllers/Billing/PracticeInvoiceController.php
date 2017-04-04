<?php

namespace App\Http\Controllers\Billing;

use App\Billing\Practices\PracticeInvoiceGenerator;
use App\Http\Controllers\Controller;
use App\PatientMonthlySummary;
use App\Practice;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;


class PracticeInvoiceController extends Controller
{

    public function make()
    {

        $practices = Practice::active();

        return view('admin.reports.billing', compact(['practices']));

    }

    public function data(Request $request)
    {
        $input = $request->input();

        $patients = Patient
            ::whereHas('patientSummaries', function ($q) {
                $q->where('ccm_time', '>', 1199)
//                    ->where('month_year', Carbon::now()->firstOfMonth()->toDateString())
                    ->where('month_year', '2017-03-01')
                    ->where('no_of_successful_calls', '>', 0);

            });

        if ($input['practice_id'] != 0) {

            $practice = $input['practice_id'];

            $patients = $patients->whereHas('user', function ($k) use
            (
                $practice

            ) {
                $k->whereProgramId($practice);
            });
        }


        $patients = $patients->orderBy('updated_at', 'desc')
            ->take(100)
            ->pluck('user_id');

        $count = 0;
        $formatted = [];

        foreach ($patients as $p) {

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

            $day_start = Carbon::parse(Carbon::now()->firstOfMonth()->format('Y-m-d'));

            $reportId = $report->id;

            if (!isset($problems[0])) {
                $problems[0] = 'N/A';
            }

            if (!isset($problems[1])) {
                $problems[1] = 'N/A';
            }

            if ($problems[0] == 'N/A' || $problems[1] == 'N/A' || $info->ccm_status == 'withdrawn' || $info->ccm_status == 'paused') {
                $checked = '';
            } else {
                $checked = 'checked';
            }

            $rejected = ($report->rejected == 1)
                ? 'checked'
                : '';

            $report->approved = $checked == ''
                ? 0
                : 1;
            $report->save();


            $name = "<a href=" . URL::route('patient.careplan.show', [
                    'patient' => $u->id,
                    'page'    => 1,
                ]) . "> " . $u->fullName . "</a>";

            $formatted[$count] = [

                'name'                   => $name,
                'provider'               => $u->billingProvider()->fullName,
                'practice'               => $u->primaryPractice->display_name,
                'dob'                    => $info->birth_date,
                'ccm'                    => round($info->cur_month_activity_time / 60, 2),
                'problem1'               => $problems[0],
                'problem2'               => $problems[1],
                'no_of_successful_calls' => $report->no_of_successful_calls,
                'status'                 => $info->ccm_status,
                'approve'                => "<input type=\"checkbox\" class='approved_checkbox' id='$reportId' $checked>",
                'reject'                 => "<input type=\"checkbox\" class='rejected_checkbox' id='$reportId' $rejected>",
                'report_id'              => $reportId ?? null,

            ];
            $count++;

        }

        $formatted = collect($formatted);

        return Datatables::of($formatted)
            ->addColumn('background_color', function ($a) {
                if ($a['problem1'] == 'N/A' || $a['problem2'] == 'N/A' || $a['status'] == 'withdrawn' || $a['status'] == 'paused') {
                    return 'rgba(255, 252, 96, 0.407843)';
                } else {
                    return '';
                }
            })
            ->make(true);

    }

    public function updateApproved(Request $request)
    {

        $input = $request->input();

        $report = PatientMonthlySummary::find($input['report_id']);

        //if approved was checked
        if ($input['approved'] == 1) {

            $report->approved = 1;
            $report->rejected = 0;

        } else {
            //approved was unchecked

            $report->approved = 0;

        }

        //if approved was unchecked, rejected stays as is. If it was approved, rejected becomes 0
        $report->actor_id = auth()->user()->id;
        $report->save();

        return $report;

    }

    public function updateRejected(Request $request)
    {

        $input = $request->input();

        $report = PatientMonthlySummary::find($input['report_id']);

        //if approved was checked
        if ($input['rejected'] == 1) {

            $report->rejected = 1;
            $report->approved = 0;

        } else {

            //rejected was unchecked

            $report->rejected = 0;

        }

        //if approved was unchecked, rejected stays as is. If it was approved, rejected becomes 0
        $report->actor_id = auth()->user()->id;
        $report->save();

        return $report;

    }

    public function makeInvoices(Request $request){

        $data = [];

        foreach ($request->input('practices') as $practiceId){

            $practice = Practice::find($practiceId);

            $data = (new PracticeInvoiceGenerator($practice, Carbon::parse('2017-03-01')))->generatePdf();

            $invoices[$data['link']] = $data['name'];

        }

        return view('billing.practice.list', compact(['invoices']));

    }

    public function createInvoices(){

        $practices = Practice::active();

        return view('billing.practice.create', compact(['practices']));
    }

}

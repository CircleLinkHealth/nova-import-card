<?php

namespace App\Http\Controllers\Billing;

use App\Billing\Practices\PracticeInvoiceGenerator;
use App\Http\Controllers\Controller;
use App\Patient;
use App\PatientMonthlySummary;
use App\Practice;
use App\Reports\ApproveBillablePatientsReport;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;


class PracticeInvoiceController extends Controller
{

    public function make()
    {

        $practices = Practice::active();

        $testDate = '2017-03-01';

        $currentMonth = '2017-03-01';

        $approved = PatientMonthlySummary
            ::where('month_year', $testDate)
            ->where('ccm_time', '>', 1199)
            ->where('approved', 1)->count();

        $rejected = PatientMonthlySummary
            ::where('month_year', $testDate)
            ->where('ccm_time', '>', 1199)
            ->where('rejected', 1)->count();

        $toQA = PatientMonthlySummary
            ::where('month_year', $testDate)
            ->where('ccm_time', '>', 1199)
            ->where('approved', 0)
            ->where('rejected', 0)
            ->count();

        return view('admin.reports.billing', compact([
            'practices',
            'currentMonth',
            'counts',
            'approved',
            'rejected',
            'toQA',
        ]));

    }

    public function data(Request $request)
    {
        $input = $request->input();

        $reporter = new ApproveBillablePatientsReport(Carbon::parse('2017-03-01'), $input['practice_id']);

        $reporter->data();

        return $reporter->format();

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

    public function makeInvoices(Request $request)
    {

        $data = [];

        foreach ($request->input('practices') as $practiceId) {

            $practice = Practice::find($practiceId);

            $data = (new PracticeInvoiceGenerator($practice, Carbon::parse('2017-03-01')))->generatePdf();

            $invoices[$practice->display_name] = $data;

        }

        return view('billing.practice.list', compact(['invoices']));

    }

    public function storeProblem(Request $request)
    {

        $input = $request->input();

        $report = PatientMonthlySummary::find($input['report_id']);

        $key = $input['problem_no'];

        if($input['select_problem'] == 'other') {

            $report->$key = $input['otherProblem'];

        } else {

            $report->$key = $input['select_problem'];

        }

        $report->save();

        return json_encode($key);


    }

    public function createInvoices()
    {

        $practices = Practice::active();
        $testDate = '2017-03-01';

        $readyToBill = [];
        $needsQA = [];
        foreach ($practices as $practice) {

            $pending = (new PracticeInvoiceGenerator($practice,
                Carbon::parse($testDate)))->checkForPendingQAForPractice();

            if ($pending) {
                $needsQA[] = $practice;
            } else {
                $readyToBill[] = $practice;
            }

        }

        return view('billing.practice.create', compact(
            [
                'needsQA',
                'readyToBill',
            ]
        ));
    }

}

<?php

namespace App\Http\Controllers\Billing;

use App\AppConfig;
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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;


class PracticeInvoiceController extends Controller
{

    public function make()
    {

        $practices = Practice::active();

        $currentMonth = '2017-03-01';

        $counts = $this->getCounts(Carbon::parse($currentMonth), $practices[0]->id);

        $approved = $counts['approved'];

        $rejected = $counts['rejected'];

        $toQA = $counts['toQA'];

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

        $reporter->dataV1();

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

        //used for view report counts
        $counts = $this->getCounts($input['date'], $input['practice_id']);

        return response()->json(
            [
                'report_id' => $report->id,
                'counts'    => $counts,
            ]
        );

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

        //used for view report counts
        $counts = $this->getCounts($input['date'], $input['practice_id']);

        return response()->json(
            [
                'report_id' => $report->id,
                'counts'    => $counts,
            ]
        );
    }

    public function createInvoices()
    {

        $practices = Practice::active();
        $testDate = '2017-03-01';

        $readyToBill = [];
        $needsQA = [];
        $invoice_no = AppConfig::where('config_key', 'billing_invoice_count')->first()['config_value'];

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
                'invoice_no'
            ]
        ));
    }

    public function makeInvoices(Request $request)
    {

        $invoices = [];

        $num = AppConfig::where('config_key', 'billing_invoice_count')->first();

        $num['config_value'] = $request->input('invoice_no');

        $num->save();

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
        $codeKey = $input['problem_no'] . '_code';

        if ($input['has_problem'] == 1) {

            $report->$codeKey = $input['code'];

        } else {

            if ($input['select_problem'] == 'other') {

                $report->$key = $input['otherProblem'];

            } else {

                $report->$key = $input['select_problem'];

            }

            $report->$codeKey = $input['code'];

        }

        //if report has both problems setup with codes, set approved to 1 here to they show up on the count for the view.
        if($report->billable_problem1_code != ''
        && ($report->billable_problem2_code != '')
        && ($report->billable_problem2 != '')
        && ($report->billable_problem1 != '')){
            $report->approved = 1;
        };

        $report->save();

        $date = Carbon::parse($input['modal_date'])->firstOfMonth()->toDateString();


        //used for view report counts
        $counts = $this->getCounts($date, $input['modal_practice_id']);

        return response()->json(
            [
                'report_id' => $report->id,
                'counts'    => $counts,
            ]
        );

    }

    public function counts(Request $request)
    {

        $date = Carbon::parse($request['date']);
        $practice = Practice::find($request['practice_id']);

        $counts = PatientMonthlySummary::getPatientQACountForPracticeForMonth($practice, $date);

        return response()->json($counts);
    }

    public function getCounts(
        $date,
        $practice
    ) {

        $date = Carbon::parse($date);
        $practice = Practice::find($practice);

        return PatientMonthlySummary::getPatientQACountForPracticeForMonth($practice, $date);

    }

    public function downloadInvoice($name){

        return response()->download(storage_path('/download/' . $name), $name, [
            'Content-Length: ' . filesize(storage_path('/download/' . $name)),
        ]);

    }

    public function send(Request $request){

        $invoices = (array) json_decode($request->input('links'));

        foreach ($invoices as $key => $value) {

            $practice = Practice::whereDisplayName($key)->first();

            $data = (array) $value;

            $patientReport = $data['Patient Report'];
            $invoice = $data['Invoice'];

            $invoiceLink = route
            (
                'monthly.billing.download',
                ['name' => $patientReport]
            );

            $logger = '';

            $recipients = explode(', ', $practice->invoice_recipients);

            $recipients = array_merge($recipients, Practice::getInvoiceRecipients($practice));

            if($practice->invoice_recipients){

                foreach($recipients as $recipient){

                    Mail::send('billing.practice.mail', ['link' => $invoiceLink], function ($m) use ($recipient, $invoice) {

                        $m->from('billing@circlelinkhealth.com', 'CircleLink Health');

                        $m->to($recipient)->subject('Your Invoice for CircleLink Health');

                        $m->attach(storage_path('/download/' . $invoice));

                    });

                    $logger .= "Sent report for $practice->name to $recipient <br />";
                }

            } else {

                $logger .= "No recipients setup for $practice->name...";


            }

        }

        return $logger;

    }


}

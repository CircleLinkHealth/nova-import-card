<?php

namespace App\Http\Controllers\Billing;

use App\AppConfig;
use App\Billing\Practices\PracticeInvoiceGenerator;
use App\Http\Controllers\Controller;
use App\PatientMonthlySummary;
use App\Practice;
use App\Services\ApproveBillablePatientsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Yajra\Datatables\Facades\Datatables;


class PracticeInvoiceController extends Controller
{
    private $service;

    /**
     * PracticeInvoiceController constructor.
     *
     * @param ApproveBillablePatientsService $service
     */
    public function __construct(ApproveBillablePatientsService $service)
    {
        $this->service = $service;
    }

    public function make()
    {

        $practices = Practice::active();

        $currentMonth = Carbon::now()->firstOfMonth()->toDateString();

        $dates = [];

        for ($i = -6; $i < 6; $i++) {
            $date = Carbon::parse($currentMonth)->addMonths($i)->firstOfMonth()->toDateString();

            $dates[$date] = Carbon::parse($date)->format('F, Y');
        }

        $counts = $this->getCounts(Carbon::parse($currentMonth), $practices[0]->id);

        return view('admin.reports.billing', compact([
            'practices',
            'currentMonth',
            'counts',
            'dates',
        ]));
    }

    public function getCounts(
        $date,
        $practice
    ) {

        $date = Carbon::parse($date);
        $practice = Practice::find($practice);

        return PatientMonthlySummary::getPatientQACountForPracticeForMonth($practice, $date);
    }

    public function data(Request $request)
    {
        $data = $this->service->patientsToApprove($request['practice_id'], Carbon::parse($request['date']));

        return Datatables::of(collect($data))
                         ->addColumn('background_color', function ($a) {
                             if ($a['lacksProblems'] || $a['status'] == 'withdrawn' || $a['status'] == 'paused' || $a['no_of_successful_calls'] < 1) {
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
        $currentMonth = Carbon::now()->firstOfMonth()->toDateString();

        $dates = [];

        for ($i = -6; $i < 6; $i++) {
            $date = Carbon::parse($currentMonth)->addMonths($i)->firstOfMonth()->toDateString();

            $dates[$date] = Carbon::parse($date)->format('F, Y');
        }

        $readyToBill = [];
        $needsQA = [];
        $invoice_no = AppConfig::where('config_key', 'billing_invoice_count')->first()['config_value'];

        $readyToBill = $practices;

//        foreach ($practices as $practice) {
//
//            $pending = (new PracticeInvoiceGenerator($practice,
//                Carbon::parse($currentMonth)))->checkForPendingQAForPractice();
//
//            if ($pending) {
//
//                $needsQA[] = $practice;
//
//            } else {
//
//                $readyToBill[] = $practice;
//
//            }
//
//        }

        return view('billing.practice.create', compact(
            [
                'needsQA',
                'readyToBill',
                'invoice_no',
                'dates',
            ]
        ));
    }

    public function makeInvoices(Request $request)
    {

        $invoices = [];

        $num = AppConfig::where('config_key', 'billing_invoice_count')->first();

        $num['config_value'] = $request->input('invoice_no');

        $num->save();

        $date = Carbon::parse($request->input('date'));

        foreach ($request->input('practices') as $practiceId) {
            $practice = Practice::find($practiceId);

            $data = (new PracticeInvoiceGenerator($practice, $date))->generatePdf();

            $invoices[$practice->display_name] = $data;
        }

        return view('billing.practice.list', compact(['invoices']));
    }

    public function storeProblem(Request $request)
    {
        $report = PatientMonthlySummary::find($request['report_id']);

        $key = $request['problem_no'];

        $report->$key = $request['ccd_problem_id'];

        if (!$this->service->lacksProblems($report)) {
            $report->approved = true;
        }

        $report->save();

        $counts = $this->getCounts($report->month_year->toDateString(), $report->patient->primaryPractice->id);

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

    public function downloadInvoice(
        $practice,
        $name
    ) {
        if (!auth()->user()->practice((int) $practice)) {
            return abort(403, 'Unauthorized action.');
        }

        return response()->download(storage_path('/download/' . $name), $name, [
            'Content-Length: ' . filesize(storage_path('/download/' . $name)),
        ]);
    }

    public function send(Request $request)
    {

        $invoices = (array)json_decode($request->input('links'));

        $logger = '';

        foreach ($invoices as $key => $value) {
            $practice = Practice::whereDisplayName($key)->first();

            $data = (array)$value;

            $patientReport = $data['Patient Report'];
            $invoice = $data['Invoice'];

            $invoiceLink = route(
                'monthly.billing.download',
                [
                    'name'     => $patientReport,
                    'practice' => $practice->id,
                ]
            );


            if ($practice->invoice_recipients != '') {
                $recipients = explode(', ', $practice->invoice_recipients);

                $recipients = array_merge($recipients, $practice->getInvoiceRecipients()->toArray());
            } else {
                $recipients = $practice->getInvoiceRecipients();
            }

            if (count($recipients) > 0) {
                foreach ($recipients as $recipient) {
                    Mail::send('billing.practice.mail', ['link' => $invoiceLink], function ($m) use (
                        $recipient,
                        $invoice
                    ) {

                        $m->from('billing@circlelinkhealth.com', 'CircleLink Health');

                        $m->to($recipient)->subject('Your Invoice and Billing Report from CircleLink');

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

<?php

namespace App\Http\Controllers\Billing;

use App\AppConfig;
use App\Billing\Practices\PracticeInvoiceGenerator;
use App\Http\Controllers\Controller;
use App\Models\CCD\Problem;
use App\Models\CPM\CpmProblem;
use App\Models\ProblemCode;
use App\PatientMonthlySummary;
use App\Practice;
use App\Services\ApproveBillablePatientsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;


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

    /**
     * Show the page to choose a practice and generate approvable billing reports
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function make()
    {
        $practices = Practice::orderBy('display_name')
                             ->active()
                             ->get();

        $cpmProblems = CpmProblem::where('name', '!=', 'Diabetes')
                                 ->get()
                                 ->map(function ($p) {
                                     return [
                                         'id'   => $p->id,
                                         'name' => $p->name,
                                         'code' => $p->default_icd_10_code,
                                     ];
                                 });

        return view('admin.reports.billing', compact([
            'cpmProblems',
            'practices',
        ]));
    }

    /**
     * Get approvable patients for a practice for a month.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        if ( ! $request->ajax()) {
            return response()->json('Method not allowed', 403);
        }

        $data = $this->service->patientsToApprove($request['practice_id'], Carbon::parse($request['date']));

        return response()->json($data);
    }

    public function updateStatus(Request $request)
    {
        if ( ! $request->ajax()) {
            return response()->json('Method not allowed', 403);
        }

        $summary = PatientMonthlySummary::find($request['report_id']);

        $summary->approved = $request['approved'];
        $summary->rejected = $request['rejected'];

        //if approved was unchecked, rejected stays as is. If it was approved, rejected becomes 0
        $summary->actor_id = auth()->user()->id;
        $summary->save();

        //used for view report counts
        $counts = $this->getCounts($summary->month_year, $summary->patient->primaryPractice->id);

        return response()->json([
            'report_id' => $summary->id,
            'counts'    => $counts,
        ]);
    }

    public function getCounts(
        $date,
        $practice
    ) {
        $date = Carbon::parse($date);

        return $this->service->counts($practice, $date->firstOfMonth());
    }

    public function createInvoices()
    {

        $practices    = Practice::active()->get();
        $currentMonth = Carbon::now()->firstOfMonth()->toDateString();

        $dates = [];

        for ($i = -6; $i < 6; $i++) {
            $date = Carbon::parse($currentMonth)->addMonths($i)->firstOfMonth()->toDateString();

            $dates[$date] = Carbon::parse($date)->format('F, Y');
        }

        $readyToBill = [];
        $needsQA     = [];
        $invoice_no  = AppConfig::where('config_key', 'billing_invoice_count')->first()['config_value'];

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
        $summary = PatientMonthlySummary::find($request['report_id']);

        $key = $request['problem_no'];

        $problemId = $request['id'];

        if ($problemId == 'Other') {
            $problemId = $this->service->storeCcdProblem($summary->patient, [
                'name'             => $request['name'],
                'cpm_problem_id'   => $request['cpm_problem_id'],
                'billable'         => true,
                'code'             => $request['code'],
                'code_system_name' => 'ICD-10',
                'code_system_oid'  => '2.16.840.1.113883.6.3',
            ])->id;
        }

        if ($problemId) {
            $existingProblemId = $summary->$key;

            if ($existingProblemId) {
                Problem::where('id', $existingProblemId)
                       ->update([
                           'billable' => false,
                       ]);
            }

            Problem::where('id', $problemId)
                   ->update([
                       'billable' => true,
                   ]);

            $updated = ProblemCode::where('problem_id', $problemId)
                                  ->where('code_system_name', 'like', '%10%')
                                  ->update([
                                      'code'             => $request['code'],
                                      'code_system_name' => 'ICD-10',
                                      'code_system_oid'  => '2.16.840.1.113883.6.3',
                                  ]);

            if ( ! $updated) {
                ProblemCode::create([
                    'problem_id'       => $problemId,
                    'code'             => $request['code'],
                    'code_system_name' => 'ICD-10',
                    'code_system_oid'  => '2.16.840.1.113883.6.3',
                ]);
            }
        }

        $summary->$key = $problemId;

        if ( ! $this->service->lacksProblems($summary)) {
            $summary->approved = true;
        }

        $summary->save();

        $counts = $this->getCounts($summary->month_year->toDateString(), $summary->patient->primaryPractice->id);

        return response()->json(
            [
                'report_id' => $summary->id,
                'counts'    => $counts,
            ]
        );
    }

    public function counts(Request $request)
    {
        $date = Carbon::parse($request['date']);

        $counts = $this->service->counts($request['practice_id'], $date->firstOfMonth());

        return response()->json($counts);
    }

    public function downloadInvoice(
        $practice,
        $name
    ) {
        if ( ! auth()->user()->practice((int)$practice)) {
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
            $invoice       = $data['Invoice'];

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

<?php

namespace App\Http\Controllers\Billing;

use App\AppConfig;
use App\ChargeableService;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApprovableBillablePatient;
use App\Models\CCD\Problem;
use App\Models\CPM\CpmProblem;
use App\Models\ProblemCode;
use App\Notifications\PracticeInvoice;
use App\PatientMonthlySummary;
use App\Practice;
use App\Chargeable;
use App\Repositories\PatientSummaryEloquentRepository;
use App\Services\ApproveBillablePatientsService;
use App\Services\PracticeReportsService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Collection;


class PracticeInvoiceController extends Controller
{
    private $patientSummaryDBRepository;
    private $service;
    private $practiceReportsService;

    /**
     * PracticeInvoiceController constructor.
     *
     * @param ApproveBillablePatientsService $service
     * @param PatientSummaryEloquentRepository $patientSummaryDBRepository
     * @param PracticeReportsService $practiceReportsService
     */
    public function __construct(
        ApproveBillablePatientsService $service,
        PatientSummaryEloquentRepository $patientSummaryDBRepository,
        PracticeReportsService $practiceReportsService
    ) {
        $this->service                    = $service;
        $this->patientSummaryDBRepository = $patientSummaryDBRepository;
        $this->practiceReportsService     = $practiceReportsService;

    }

    /**
     * Show the page to choose a practice and generate approvable billing reports
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function make()
    {
        $practices = Practice::orderBy('display_name')
                             ->authUserCanAccess()
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

        $chargeableServices = ChargeableService::all();

        return view('admin.reports.billing', compact([
            'cpmProblems',
            'practices',
            'chargeableServices',
        ]));
    }

    public function getChargeableServices() {
        return $this->ok(ChargeableService::all());
    }

    /**
     * Get approvable patients for a practice for a month.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
     public function data(Request $request)
     {
         $practice_id = $request->input('practice_id');
         $date = $request->input('date');
         if ($date) {
             $date = Carbon::createFromFormat('M, Y', $date);
         }
         else {
             return $this->badRequest('Invalid [date] parameter. Must have a value like "Jan, 2017"');
         }
         $summaries = $this->service->billablePatientSummaries($practice_id, $date)
                                    ->paginate(100);

         $summaries->getCollection()->transform(function ($summary) {
             $result = $this->patientSummaryDBRepository
                 ->attachBillableProblems($summary->patient, $summary);

             $data = $summary;

             if ($result) {
                 $data = $result;
             }

             return ApprovableBillablePatient::make($data);
         });

         return $summaries;
     }

     public function updatePatientChargeableServices(Request $request) {
         
     }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePracticeChargeableServices(Request $request)
    {
        $practice_id = $request->input('practice_id');
        $date = $request->input('date');
        if ($date) {
            $date = Carbon::createFromFormat('M, Y', $date);
        }
        else {
            return $this->badRequest('Invalid [date] parameter. Must have a value like "Jan, 2017"');
        }
        $summaries = $this->service->billablePatientSummaries($practice_id, $date)
            ->paginate(100);

        $summaries->getCollection()->transform(function ($summary) use ($request) {
            $result = $this->patientSummaryDBRepository
                ->attachBillableProblems($summary->patient, $summary);

            if ($result) {
                $summary = $result;
            }

            $summary->sync($request['default_code_id']);

            return ApprovableBillablePatient::make($summary);
        });

        return $summaries;
    }

    public function updateSummaryChargeableServices(Request $request)
    {
        if ( ! $request->ajax()) {
            return response()->json('Method not allowed', 403);
        }

        $reportId = $request->input('report_id');

        if (!$reportId) {
            return $this->badRequest('report_id is a required field');
        }

        //need array of IDs
        $chargeableIDs = $request->input('patient_chargeable_services');

        if (!is_array($chargeableIDs)) {
            return $this->badRequest('patient_chargeable_services must be an array');
        }
        else {
            $chargeableIDs = new Collection($chargeableIDs);
        }


        $summary = PatientMonthlySummary::where('patient_id', $reportId)->first();

        if (!$summary) {
            return $this->badRequest("Report with id $reportId not found.");
        }

        $summary->chargeables()->delete();

        $chargeableIDs->map(function ($id) use ($reportId) {
            $chargeable = new Chargeable();
            $chargeable->chargeable_service_id = $id;
            $chargeable->chargeable_id = $reportId;
            $chargeable->chargeable_type = PatientMonthlySummary::class;
            $chargeable->save();
            return $chargeable;
        });

        //$summary->chargeableServices()->sync($chargeableServices);

        return $this->ok($summary);

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
            'status'    => [
                'approved' => $summary->approved,
                'rejected' => $summary->rejected,
            ],
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
        $currentMonth = Carbon::now()->firstOfMonth();

        $dates = [];

        for ($i = 0; $i <= 6; $i++) {
            $date = $currentMonth->copy()->subMonth($i)->firstOfMonth();

            $dates[$date->toDateString()] = $date->format('F, Y');
        }

        $readyToBill = Practice::active()
                               ->authUserCanAccess()
                               ->get();
        $needsQA     = [];
        $invoice_no  = AppConfig::where('config_key', 'billing_invoice_count')->first()['config_value'];

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

        $date = Carbon::parse($request->input('date'));

        if ($request['format'] == 'pdf') {

            $invoices = $this->practiceReportsService->getPdfInvoiceAndPatientReport($request['practices'], $date);

            return view('billing.practice.list', compact(['invoices']));

        } elseif ($request['format'] == 'csv' or 'xls') {

            $invoices = $this->practiceReportsService->getQuickbooksReport($request['practices'], $request['format'],
                $date);

            return response()->download($invoices['full'], $invoices['file'], [
                'Content-Length: ' . filesize($invoices['full']),
            ]);
        }
    }

    public function storeProblem(Request $request)
    {
        try {
            $summary = PatientMonthlySummary::find($request['report_id']);

            $key = $request['problem_no'];

            $problemId = $request['id'];

            if (in_array(strtolower($problemId), ['other', 'new'])) {
                $problemId = $this->patientSummaryDBRepository->storeCcdProblem($summary->patient, [
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
                           'name'     => $request['name'],
                       ]);

                $updated = ProblemCode::where('problem_id', $problemId)
                                      ->where('code_system_name', 'like', '%10%')
                                      ->update([
                                          'code'             => $request['code'],
                                          'code_system_name' => 'ICD-10',
                                          'code_system_oid'  => '2.16.840.1.113883.6.3',
                                      ]);

                if ( ! $updated && $request['code']) {
                    ProblemCode::create([
                        'problem_id'       => $problemId,
                        'code'             => $request['code'],
                        'code_system_name' => 'ICD-10',
                        'code_system_oid'  => '2.16.840.1.113883.6.3',
                    ]);
                }
            }

            $summary->$key = $problemId;

            if ( ! $this->patientSummaryDBRepository->lacksProblems($summary)) {
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
        } catch (\Exception $e) {
            return response()->json([
                'message'    => $e->getMessage(),
                'stacktrace' => $e->getTraceAsString(),
            ], $e->getCode());
        }
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
        if ( ! auth()->user()->practice((int)$practice) && ! auth()->user()->hasRole('administrator')) {
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
            $invoicePath   = storage_path('/download/' . $data['Invoice']);

            $invoiceLink = route(
                'monthly.billing.download',
                [
                    'name'     => $patientReport,
                    'practice' => $practice->id,
                ]
            );

            if ($practice->invoice_recipients != '') {
                $recipients = $practice->getInvoiceRecipientsArray();

                $recipients = array_merge($recipients, $practice->getInvoiceRecipients()->pluck('email')->all());
            } else {
                $recipients = $practice->getInvoiceRecipients()->pluck('email')->all();
            }

            if (count($recipients) > 0) {
                foreach ($recipients as $recipient) {
                    $user = User::whereEmail($recipient)->first();

                    $notification = new PracticeInvoice($invoiceLink, $invoicePath);

                    if ($user) {
                        $user->notify($notification);
                    } else {
                        Notification::route('mail', $recipient)
                                    ->notify($notification);
                    }

                    $logger .= "Sent report for $practice->name to $recipient <br />";
                }
            } else {
                $logger .= "No recipients setup for $practice->name...";
            }
        }

        return $logger;
    }
}

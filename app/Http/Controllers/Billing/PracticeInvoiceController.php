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
use App\Repositories\PatientSummaryEloquentRepository;
use App\Services\ApproveBillablePatientsService;
use App\Services\PracticeReportsService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;


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
                             ->select(['name', 'id', 'display_name'])
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

        $currentMonth = Carbon::now()->startOfMonth();

        $dates = [];

        $oldestSummary = PatientMonthlySummary::orderBy('created_at', 'asc')->first();

        $numberOfMonths = $currentMonth->diffInMonths($oldestSummary->created_at) ?? 12;

        for ($i = 0; $i <= $numberOfMonths; $i++) {
            $date = $currentMonth->copy()->subMonth($i)->startOfMonth();

            $dates[] = [
                'label' => $date->format('F, Y'),
                'value' => $date->toDateString(),
            ];
        }

        return view('admin.reports.billing', compact([
            'cpmProblems',
            'practices',
            'chargeableServices',
            'dates',
        ]));
    }

    public function getChargeableServices()
    {
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
        $date        = $request->input('date');
        if ($date) {
            $date = Carbon::createFromFormat('M, Y', $date);
        } else {
            return $this->badRequest('Invalid [date] parameter. Must have a value like "Jan, 2017"');
        }

        $summaries = $this->service->billablePatientSummaries($practice_id, $date)->paginate(100);

        $summaries->getCollection()->transform(function ($summary) {
            if ( ! $summary->actor_id) {
                $summary = $this->patientSummaryDBRepository->attachChargeableServices($summary->patient, $summary);
                $summary = $this->patientSummaryDBRepository->attachBillableProblems($summary->patient, $summary);
            }

            return ApprovableBillablePatient::make($summary);
        });

        $isClosed = ! ! $summaries->getCollection()->every(function ($summary) {
            return ! ! $summary->actor_id;
        });

        return response($summaries)->header('is-closed', (int)$isClosed);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePracticeChargeableServices(Request $request)
    {
        $practice_id     = $request->input('practice_id');
        $date            = $request->input('date');
        $default_code_id = $request->input('default_code_id');
        $is_detach       = $request->has('detach');

        if ($date) {
            $date = Carbon::createFromFormat('M, Y', $date);
        } else {
            return $this->badRequest('Invalid [date] parameter. Must have a value like "Jan, 2017"');
        }

        if ( ! $default_code_id || ! $practice_id) {
            return $this->badRequest('Invalid [practice_id] and [default_code_id] parameters. Must have a values');
        }

        $summaries = $this->service
            ->billablePatientSummaries($practice_id, $date)
            ->get()
            ->map(function ($summary) use ($default_code_id, $is_detach) {
                $result = $this->patientSummaryDBRepository
                    ->attachBillableProblems($summary->patient, $summary);

                if ($result) {
                    $summary = $result;
                }

                if ( ! $is_detach) {
                    $summary = $this->service
                        ->attachDefaultChargeableService($summary, $default_code_id, false);
                } else {
                    $summary = $this->service
                        ->detachDefaultChargeableService($summary, $default_code_id);
                }


                return ApprovableBillablePatient::make($summary);
            });

        return response()->json($summaries);
    }

    public function updateSummaryChargeableServices(Request $request)
    {
        if ( ! $request->ajax()) {
            return response()->json('Method not allowed', 403);
        }

        $reportId = $request->input('report_id');

        if ( ! $reportId) {
            return $this->badRequest('report_id is a required field');
        }

        //need array of IDs
        $chargeableIDs = $request->input('patient_chargeable_services');

        if ( ! is_array($chargeableIDs)) {
            return $this->badRequest('patient_chargeable_services must be an array');
        }

        $summary = PatientMonthlySummary::find($reportId);

        $summary->actor_id = auth()->id();
        $summary->save();

        if ( ! $summary) {
            return $this->badRequest("Report with id $reportId not found.");
        }

        $summary->chargeableServices()->sync($chargeableIDs);

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
            'actor_id'  => $summary->actor_id,
        ]);
    }

    /** open patient-monthly-summaries in a practice */
    public function openMonthlySummaryStatus(Request $request)
    {
        $practice_id = $request->input('practice_id');
        $date        = $request->input('date');
        $user        = auth()->user();

        if ($date) {
            $date = Carbon::createFromFormat('M, Y', $date);
        }

        $summaries = PatientMonthlySummary::whereHas('patient', function ($q) use ($practice_id) {
            return $q->where('program_id', $practice_id);
        })->where('month_year', $date->startOfMonth());

        $summaries->update([
            'actor_id' => null,
        ]);

        return response()->json($summaries->get());
    }

    /** open patient-monthly-summaries in a practice */
    public function closeMonthlySummaryStatus(Request $request)
    {
        $practice_id = $request->input('practice_id');
        $date        = $request->input('date');
        $user        = auth()->user();

        if ($date) {
            $date = Carbon::createFromFormat('M, Y', $date);
        }

        $summaries = PatientMonthlySummary::whereHas('patient', function ($q) use ($practice_id) {
            return $q->where('program_id', $practice_id);
        })->where('month_year', $date->startOfMonth());

        $summaries->update([
            'actor_id' => $user->id,
            'needs_qa' => false,
        ]);

        return response()->json($summaries->get());
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
        $currentMonth = Carbon::now()->startOfMonth();

        $dates = [];

        $oldestSummary = PatientMonthlySummary::orderBy('created_at', 'asc')->first();

        $numberOfMonths = $currentMonth->diffInMonths($oldestSummary->created_at) ?? 12;

        for ($i = 0; $i <= $numberOfMonths; $i++) {
            $date = $currentMonth->copy()->subMonth($i)->startOfMonth();

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

    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function makeInvoices(Request $request)
    {

        $invoices = [];

        $date = Carbon::parse($request->input('date'));

        if ($request['format'] == 'pdf') {

            $invoices = $this->practiceReportsService->getPdfInvoiceAndPatientReport($request['practices'], $date);

            return view('billing.practice.list', compact(['invoices']));

        } elseif ($request['format'] == 'csv' or 'xls') {

            $report = $this->practiceReportsService->getQuickbooksReport($request['practices'], $request['format'],
                $date);

            return $this->downloadMedia($report);
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
                    'is_monitored'     => true,
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
                           'billable'     => true,
                           'name'         => $request['name'],
                           'is_monitored' => true,
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

            if ($key == 'problem_1' || $key == 'problem_2') {
                $summary->$key = $problemId;
            }
            else if ($key == 'bhi_problem') {
                if ($summary->hasServiceCode('CPT 99484')) {
                    $summaryProblem = $summary->billableProblems()->wherePivot('type', 'bhi')->first();
                    if ($summaryProblem) {
                        $summary->billableProblems()->updateExistingPivot($problemId, [
                            'name' => $request['name'],
                            'icd_10_code' => $request['code']
                        ]);
                    }
                    else {
                        $summary->attachBillableProblem($problemId, $request['name'], $request['code'], 'bhi');
                    }
                }
                else {
                    throw new \Exception('cannot set bhi_problem because practice is not chargeable for CPT 99484');
                }
            }

            if ( ! $this->patientSummaryDBRepository->lacksProblems($summary)) {
                $summary->approved = true;
            }

            $problemNumber = extractNumbers($key);

            if ((int)$problemNumber > 0 && (int)$problemNumber < 3) {
                $summary->{"billable_problem$problemNumber"}        = $request['name'];
                $summary->{"billable_problem{$problemNumber}_code"} = $request['code'];
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
                'code' => $e->getCode()
            ], 500);
        }
    }

    public function counts(Request $request)
    {
        $date = Carbon::createFromFormat('M, Y', $request->input('date'));

        $counts = $this->service->counts($request['practice_id'], $date->firstOfMonth());

        return response()->json($counts);
    }

    public function send(Request $request)
    {

        $invoices = (array)json_decode($request->input('links'));

        $logger = '';

        foreach ($invoices as $key => $value) {
            $practice = Practice::whereDisplayName($key)->first();

            $data = (array)$value;

            $patientReportUrl = $data['patient_report_url'];
            $invoiceURL       = $data['invoice_url'];

            if ($practice->invoice_recipients != '') {
                $recipients = $practice->getInvoiceRecipientsArray();

                $recipients = array_merge($recipients, $practice->getInvoiceRecipients()->pluck('email')->all());
            } else {
                $recipients = $practice->getInvoiceRecipients()->pluck('email')->all();
            }

            if (count($recipients) > 0) {
                foreach ($recipients as $recipient) {
                    $user = User::whereEmail($recipient)->first();

                    $notification = new PracticeInvoice($patientReportUrl, $invoiceURL);

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

    /**
     * @deprecated This will be phased out. It's here only to support older links
     *
     * @param $practice
     * @param $name
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|void
     */
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
}

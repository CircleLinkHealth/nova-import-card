<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Billing;

use App\AppConfig;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApprovableBillablePatient;
use App\Models\CCD\Problem;
use App\Models\CPM\CpmProblem;
use App\Models\ProblemCode;
use App\Notifications\PracticeInvoice;
use App\Repositories\PatientSummaryEloquentRepository;
use App\Services\ApproveBillablePatientsService;
use App\Services\PracticeReportsService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class PracticeInvoiceController extends Controller
{
    private $patientSummaryDBRepository;
    private $practiceReportsService;
    private $service;

    /**
     * PracticeInvoiceController constructor.
     *
     * @param ApproveBillablePatientsService   $service
     * @param PatientSummaryEloquentRepository $patientSummaryDBRepository
     * @param PracticeReportsService           $practiceReportsService
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

    /** open patient-monthly-summaries in a practice */
    public function closeMonthlySummaryStatus(Request $request)
    {
        $practice_id = $request->input('practice_id');
        $date        = $request->input('date');
        $user        = auth()->user();

        //since this route is also accessible from software-only,
        //we should make sure that software-only role is applied on this practice
        if ( ! $user->isAdmin() && ! $user->hasRoleForSite('software-only', $practice_id)) {
            abort(403);
        }

        if ($date) {
            $date = Carbon::createFromFormat('M, Y', $date);
        }

        $summaries = $this->getCurrentMonthSummariesQuery($practice_id, $date)
            ->get();

        foreach ($summaries as $summary) {
            $summary->actor_id = $user->id;
            $summary->needs_qa = false;
            if ($summary->patient) {
                if ($summary->patient->patientInfo) {
                    $summary->closed_ccm_status = $summary->patient->patientInfo->ccm_status;
                }
            }
            $summary->save();
        }

        return response()->json($summaries);
    }

    public function counts(Request $request)
    {
        $practice_id = $request['practice_id'];

        //since this route is also accessible from software-only,
        //we should make sure that software-only role is applied on this practice
        $user = auth()->user();
        if ( ! $user->isAdmin() && ! $user->hasRoleForSite('software-only', $practice_id)) {
            abort(403);
        }

        $date = Carbon::createFromFormat('M, Y', $request->input('date'));

        $counts = $this->service->counts($practice_id, $date->firstOfMonth());

        return response()->json($counts);
    }

    public function createInvoices()
    {
        $currentMonth = Carbon::now()->startOfMonth();

        $dates = [];

        $oldestSummary = PatientMonthlySummary::orderBy('created_at', 'asc')->first();

        $numberOfMonths = $currentMonth->diffInMonths($oldestSummary->created_at) ?? 12;

        for ($i = 0; $i <= $numberOfMonths; ++$i) {
            $date = $currentMonth->copy()->subMonth($i)->startOfMonth();

            $dates[$date->toDateString()] = $date->format('F, Y');
        }

        $readyToBill = Practice::active()
            ->authUserCanAccess()
            ->get();
        $needsQA    = [];
        $invoice_no = AppConfig::where('config_key', 'billing_invoice_count')->first()['config_value'];

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

        //since this route is also accessible from software-only,
        //we should make sure that software-only role is applied on this practice
        $user = auth()->user();
        if ( ! $user->isAdmin() && ! $user->hasRoleForSite('software-only', $practice_id)) {
            abort(403);
        }

        if ($date) {
            $date = Carbon::createFromFormat('M, Y', $date);
        } else {
            return $this->badRequest('Invalid [date] parameter. Must have a value like "Jan, 2017"');
        }

        $month = $this->service->getBillablePatientsForMonth($practice_id, $date);

        return response($month['summaries'])->header('is-closed', (int) $month['is_closed']);
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
        if ( ! auth()->user()->practice((int) $practice) && ! auth()->user()->isAdmin()) {
            return abort(403, 'Unauthorized action.');
        }

        return response()->download(storage_path('/download/'.$name), $name, [
            'Content-Length: '.filesize(storage_path('/download/'.$name)),
        ]);
    }

    public function getChargeableServices()
    {
        return $this->ok(ChargeableService::all());
    }

    public function getCounts(
        $date,
        $practice
    ) {
        $date = Carbon::parse($date);

        return $this->service->counts($practice, $date->firstOfMonth());
    }

    /**
     * Show the page to choose a practice and generate approvable billing reports.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function make()
    {
        $practices = Practice::orderBy('display_name')
            ->select(['name', 'id', 'display_name'])
            ->with('chargeableServices')
            ->authUserCanAccess(auth()->user()->isSoftwareOnly())
            ->active()
            ->get();

        $cpmProblems = CpmProblem::where('name', '!=', 'Diabetes')
            ->get()
            ->map(function ($p) {
                return [
                    'id'            => $p->id,
                    'name'          => $p->name,
                    'code'          => $p->default_icd_10_code,
                    'is_behavioral' => $p->is_behavioral,
                ];
            });

        $currentMonth = Carbon::now()->startOfMonth();

        $dates = [];

        $oldestSummary = PatientMonthlySummary::orderBy('created_at', 'asc')->first();

        $numberOfMonths = $currentMonth->diffInMonths($oldestSummary->created_at) ?? 12;

        for ($i = 0; $i <= $numberOfMonths; ++$i) {
            $date = $currentMonth->copy()->subMonth($i)->startOfMonth();

            $dates[] = [
                'label' => $date->format('F, Y'),
                'value' => $date->toDateString(),
            ];
        }

        $chargeableServices = ChargeableService::all();

        return view('admin.reports.billing', compact([
            'cpmProblems',
            'practices',
            'chargeableServices',
            'dates',
        ]));
    }

    /**
     * @param Request $request
     *
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function makeInvoices(Request $request)
    {
        $invoices = [];

        $date = Carbon::parse($request->input('date'));

        if ('pdf' == $request['format']) {
            $invoices = $this->practiceReportsService->getPdfInvoiceAndPatientReport($request['practices'], $date);

            return view('billing.practice.list', compact(['invoices']));
        }
        if ('csv' == $request['format'] or 'xls') {
            $report = $this->practiceReportsService->getQuickbooksReport(
                $request['practices'],
                $request['format'],
                $date
            );

            if (false === $report) {
                return 'No data found. Please hit back and try again.';
            }

            return $this->downloadMedia($report);
        }
    }

    /** open patient-monthly-summaries in a practice */
    public function openMonthlySummaryStatus(Request $request)
    {
        $practice_id = $request->input('practice_id');
        $date        = $request->input('date');
        $user        = auth()->user();

        //since this route is also accessible from software-only,
        //we should make sure that software-only role is applied on this practice
        if ( ! $user->isAdmin() && ! $user->hasRoleForSite('software-only', $practice_id)) {
            abort(403);
        }

        if ($date) {
            $date = Carbon::createFromFormat('M, Y', $date);
        }

        $query = $this->getCurrentMonthSummariesQuery($practice_id, $date);

        $query->update([
            'actor_id'          => null,
            'closed_ccm_status' => null,
        ]);

        $summaries = $query->get();

        return response()->json($summaries);
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    public function send(Request $request)
    {
        $invoices = (array) json_decode($request->input('links'));

        $logger = '';

        foreach ($invoices as $key => $value) {
            $practice = Practice::whereDisplayName($key)->first();

            $data = (array) $value;

            $patientReportUrl = $data['patient_report_url'];
            $invoiceURL       = $data['invoice_url'];

            if ( ! empty($practice->invoice_recipients)) {
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

                    $logger .= "Sent report for {$practice->name} to ${recipient} <br />";
                }
            } else {
                $logger .= "No recipients setup for {$practice->name}...";
            }
        }

        return $logger;
    }

    public function storeProblem(Request $request)
    {
        try {
            $summary = PatientMonthlySummary::find($request['report_id']);

            //since this route is also accessible from software-only,
            //we should make sure that software-only role is applied on this practice
            $user = auth()->user();
            if ( ! $user->isAdmin()) {
                $patientPracticeId = User::find($summary->patient_id, ['program_id'])->program_id;
                if ( ! $user->isAdmin() && ! $user->hasRoleForSite('software-only', $patientPracticeId)) {
                    abort(403);
                }
            }

            $key = $request['problem_no'];

            $problemId = $request['id'];

            if (in_array(strtolower($problemId), ['other', 'new'])) {
                $newProblem = $this->patientSummaryDBRepository->storeCcdProblem($summary->patient, [
                    'name'             => $request['name'],
                    'cpm_problem_id'   => $request['cpm_problem_id'],
                    'billable'         => true,
                    'code'             => $request['code'],
                    'code_system_name' => 'ICD-10',
                    'code_system_oid'  => '2.16.840.1.113883.6.3',
                    'is_monitored'     => true,
                ]);

                $problemId = optional($newProblem)->id;
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

            if ('problem_1' == $key || 'problem_2' == $key) {
                $summary->$key = $problemId;
            } elseif ('bhi_problem' == $key && $summary->hasServiceCode('CPT 99484')) {
                if ($request['cpm_problem_id']) {
                    $cpmProblem = CpmProblem::where('id', $request['cpm_problem_id'])->where(
                        'is_behavioral',
                        1
                    )->exists();

                    if ( ! $cpmProblem) {
                        throw new \Exception('Please select a BHI problem.');
                    }
                }

                if ($summary->billableProblems()->where((new Problem())->getTable().'.id', $problemId)->exists()) {
                    $summary->billableProblems()->updateExistingPivot($problemId, [
                        'name'        => $request['name'],
                        'icd_10_code' => $request['code'],
                    ]);
                } else {
                    $summary->attachBillableProblem($problemId, $request['name'], $request['code'], 'bhi');
                }
            } else {
                throw new \Exception('Cannot add BHI problem because practice does not have service CPT 99484 activated.');
            }

            if ( ! $this->patientSummaryDBRepository->lacksProblems($summary)) {
                $summary->approved = true;
            }

            $problemNumber = extractNumbers($key);

            if ((int) $problemNumber > 0 && (int) $problemNumber < 3) {
                $summary->{"billable_problem${problemNumber}"}      = $request['name'];
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
                'code'       => $e->getCode(),
            ], 500);
        }
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
            'actor_id' => $summary->actor_id,
        ]);
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
            return $this->badRequest("Report with id ${reportId} not found.");
        }

        $summary->chargeableServices()->sync($chargeableIDs);

        return $this->ok($summary);
    }

    /**
     * @param $practice_id
     * @param Carbon $date
     *
     * @return \CircleLinkHealth\Customer\Entities\PatientMonthlySummary|\Illuminate\Database\Eloquent\Builder
     */
    private function getCurrentMonthSummariesQuery($practice_id, Carbon $date)
    {
        return PatientMonthlySummary::with('patient.patientInfo')
            ->whereHas('patient', function ($q) use ($practice_id) {
                $q->ofPractice($practice_id);
            })
            ->where('month_year', $date->startOfMonth());
    }
}

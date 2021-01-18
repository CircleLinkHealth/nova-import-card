<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Http\Controllers;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Http\Resources\ApprovableBillablePatient;
use CircleLinkHealth\CcmBilling\Jobs\CreatePracticeInvoice;
use CircleLinkHealth\CcmBilling\Notifications\PracticeInvoice;
use CircleLinkHealth\CcmBilling\Services\ApproveBillablePatientsService;
use CircleLinkHealth\CcmBilling\Services\PracticeReportsService;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Core\Traits\ApiReturnHelpers;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Repositories\PatientSummaryEloquentRepository;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Notification;

class PracticeInvoiceController extends Controller
{
    use ApiReturnHelpers;

    private $patientSummaryDBRepository;
    private $practiceReportsService;
    private $service;

    /**
     * PracticeInvoiceController constructor.
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
        $invoice_no = AppConfig::pull('billing_invoice_count', 0);

        return view('ccmbilling::create', compact(
            [
                'readyToBill',
                'invoice_no',
                'dates',
            ]
        ));
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
        return $this->ok(ChargeableService::cached());
    }

    public function getCounts(
        $date,
        $practice
    ) {
        $date = Carbon::parse($date);

        return $this->service->counts($practice, $date->firstOfMonth());
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function makeInvoices(Request $request)
    {
        $date      = Carbon::parse($request->input('date'));
        $format    = $request['format'];
        $practices = $request['practices'];

        CreatePracticeInvoice::dispatch($practices, $date, $format, auth()->id())->onQueue(getCpmQueueName(CpmConstants::LOW_QUEUE));

        $practices = Practice::whereIn('id', $practices)->pluck('display_name')->all();

        $niceDate = "{$date->shortEnglishMonth} {$date->year}";

        session()->put(
            'messages',
            array_merge(
                ["We are creating reports for $niceDate, for the following practices:"],
                $practices,
                ['We will send you an email when they are ready.']
            )
        );

        return redirect()->back();
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

        $updated = $this->getCurrentMonthSummariesQuery($practice_id, $date)->update([
            'actor_id'          => null,
            'closed_ccm_status' => null,
        ]);

        return response()->json([
            'updated' => $updated,
        ]);
    }

    /**
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

    /**
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

        if ( ! $summary->approved && ! $summary->rejected) {
            $summary->needs_qa = true;
        }

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

        $toSync = [];

        foreach ($chargeableIDs as $id) {
            $toSync[$id] = [
                'is_fulfilled' => true,
            ];
        }

        $summary->chargeableServices()->sync($toSync);

        return $this->ok($summary);
    }
}

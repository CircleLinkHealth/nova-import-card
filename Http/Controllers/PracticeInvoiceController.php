<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Http\Controllers;

use Carbon\Carbon;
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
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Http\Controllers;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus;
use CircleLinkHealth\CcmBilling\Http\Requests\PracticeInvoiceControllerMakeInvoiceRequest;
use CircleLinkHealth\CcmBilling\Jobs\CreatePracticeInvoice;
use CircleLinkHealth\CcmBilling\Jobs\CreatePracticesInvoices;
use CircleLinkHealth\CcmBilling\Services\ApproveBillablePatientsService;
use CircleLinkHealth\CcmBilling\Services\PracticeReportsService;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Core\Traits\ApiReturnHelpers;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\SharedModels\Repositories\PatientSummaryEloquentRepository;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

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

    public function index(Request $request)
    {
        $currentMonth   = Carbon::now()->startOfMonth();
        $version        = $request->input('version', 2);
        $numberOfMonths = $this->getNumberOfMonths($request, $currentMonth);
        $dates          = [];

        for ($i = 0; $i <= $numberOfMonths; ++$i) {
            $date = $currentMonth->copy()->subMonths($i)->startOfMonth();

            $dates[$date->toDateString()] = $date->format('F, Y');
        }

        $readyToBill = Practice::active()
            ->authUserCanAccess(auth()->user()->isSoftwareOnly())
            ->get();

        $invoice_no = AppConfig::pull('billing_invoice_count', 0);

        return view('ccmbilling::create', compact(
            [
                'readyToBill',
                'invoice_no',
                'dates',
                'version',
            ]
        ));
    }

    public function makeInvoices(PracticeInvoiceControllerMakeInvoiceRequest $request)
    {
        $date      = Carbon::parse($request->input('date'));
        $format    = $request->input('format');
        $practices = $request->input('practices');

        $version = intval($request->input('version', 2));
        if ($version < 3) {
            CreatePracticeInvoice::dispatch($practices, $date, $format, auth()->id())
                ->onQueue(getCpmQueueName(CpmConstants::LOW_QUEUE));
        } else {
            CreatePracticesInvoices::dispatch($practices, $date->toDateString(), $format, auth()->id())
                ->onQueue(getCpmQueueName(CpmConstants::LOW_QUEUE));
        }
        $practices = Practice::whereIn('id', $practices)->pluck('display_name')->all();
        $niceDate  = "{$date->shortEnglishMonth} {$date->year}";

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

    private function getNumberOfMonths(Request $request, Carbon $startMonth)
    {
        $version = intval($request->input('version', 2));
        if ($version < 3) {
            $oldestSummary = PatientMonthlySummary::orderBy('created_at', 'asc')->first();
        } else {
            $oldestSummary = PatientMonthlyBillingStatus::orderBy('created_at', 'asc')->first();
        }

        if ( ! $oldestSummary) {
            return 12;
        }

        return $startMonth->diffInMonths($oldestSummary->created_at) ?? 12;
    }
}

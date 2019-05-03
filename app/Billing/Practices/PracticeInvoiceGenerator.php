<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Billing\Practices;

use App\AppConfig;
use App\ChargeableService;
use App\Repositories\PatientSummaryEloquentRepository;
use App\Services\PdfService;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;

class PracticeInvoiceGenerator
{
    private $month;
    private $patients;
    /**
     * @var PatientSummaryEloquentRepository
     */
    private $patientSummaryEloquentRepository;
    private $practice;

    /**
     * PracticeInvoiceGenerator constructor.
     *
     * @param Practice                         $practice
     * @param Carbon                           $month
     * @param PatientSummaryEloquentRepository $patientSummaryEloquentRepository
     */
    public function __construct(
        Practice $practice,
        Carbon $month
    ) {
        $this->practice                         = $practice;
        $this->month                            = $month->firstOfMonth();
        $this->patientSummaryEloquentRepository = app(PatientSummaryEloquentRepository::class);
    }

    public function array_orderby()
    {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = [];
                foreach ($data as $key => $row) {
                    $tmp[$key] = $row[$field];
                }
                $args[$n] = $tmp;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);

        return array_pop($args);
    }

    /**
     * @param bool $withItemized
     *
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     *
     * @return array
     */
    public function generatePdf($withItemized = true)
    {
        $invoiceName = trim($this->practice->name).'-'.$this->month->toDateString().'-invoice';

        $pdfInvoice = $this->makeInvoicePdf($invoiceName);

        $data = [
            'invoice_url' => $pdfInvoice->getUrl(),
        ];

        if ($withItemized) {
            $reportName       = trim($this->practice->name).'-'.$this->month->toDateString().'-patients';
            $pdfPatientReport = $this->makePatientReportPdf($reportName);

            $data['patient_report_url'] = $pdfPatientReport->getUrl();
        }

        $data['practiceId'] = $this->practice->id;

        return $data;
    }

    public function getInvoiceData($chargeableServiceId = null)
    {
        $practiceId = $this->practice->id;

        if ($chargeableServiceId) {
            //if software only exists on summary we bill for that
            //if service is not software only we count summaries that have this chargeableService and NOT software only.
            $isSoftwareOnly = 'Software-Only' == ChargeableService::find($chargeableServiceId)->code;

            $billable = User::ofType('participant')
                ->ofPractice($this->practice->id)
                ->whereHas(
                                'patientSummaries',
                                function ($query) use ($chargeableServiceId, $isSoftwareOnly) {
                                    $query->whereHas(
                                        'chargeableServices',
                                        function ($query) use ($chargeableServiceId) {
                                            $query->where('id', $chargeableServiceId);
                                        }
                                    )
                                        ->where('month_year', $this->month->toDateString())
                                        ->where('approved', '=', true)
                                        ->when( ! $isSoftwareOnly, function ($q) {
                                              $q->whereDoesntHave('chargeableServices', function ($query) {
                                                  $query->where('code', 'Software-Only');
                                              });
                                          });
                                }
                            )
                ->count() ?? 0;
        } else {
            $billable = User::ofType('participant')
                ->ofPractice($this->practice->id)
                ->whereHas('patientSummaries', function ($query) {
                                $query->where('month_year', $this->month->toDateString())
                                    ->where('approved', '=', true);
                            })
                ->count() ?? 0;
        }

        return [
            'clh_address'    => $this->practice->getAddress(),
            'bill_to'        => $this->practice->bill_to_name,
            'practice'       => $this->practice,
            'month'          => $this->month->format('F, Y'),
            'rate'           => $this->practice->clh_pppm,
            'invoice_num'    => $this->incrementInvoiceNo(),
            'invoice_date'   => Carbon::today()->toDateString(),
            'due_by'         => Carbon::today()->addDays($this->practice->term_days)->toDateString(),
            'invoice_amount' => number_format(round((float) $this->practice->clh_pppm * $billable, 2), 2),
            'billable'       => $billable,
        ];
    }

    public function getItemizedPatientData()
    {
        $data          = [];
        $data['name']  = $this->practice->display_name;
        $data['month'] = $this->month->toDateString();

        $patients = User::orderBy('first_name', 'asc')
            ->ofType('participant')
            ->with([
                'patientSummaries' => function ($q) {
                    $q
                        ->with(['billableBhiProblems'])
                        ->where('month_year', $this->month->toDateString())
                        ->where('approved', '=', true);
                },
                'billingProvider',
            ])
            ->whereProgramId($this->practice->id)
            ->whereHas('patientSummaries', function ($query) {
                            $query->where('month_year', $this->month->toDateString())
                                ->where('approved', '=', true);
                        })
            ->chunk(500, function ($patients) use (&$data) {
                            foreach ($patients as $u) {
                                $summary = $u->patientSummaries->first();

                                if ( ! $this->patientSummaryEloquentRepository->hasBillableProblemsNameAndCode($summary)) {
                                    $summary = $this->patientSummaryEloquentRepository->fillBillableProblemsNameAndCode($summary);
                                    $summary->save();
                                }

                                $data['patientData'][$u->id]['ccm_time'] = round($summary->ccm_time / 60, 2);
                                $data['patientData'][$u->id]['bhi_time'] = round($summary->bhi_time / 60, 2);
                                $data['patientData'][$u->id]['name'] = $u->getFullName();
                                $data['patientData'][$u->id]['dob'] = $u->getBirthDate();
                                $data['patientData'][$u->id]['practice'] = $u->program_id;
                                $data['patientData'][$u->id]['provider'] = $u->getBillingProviderName();
                                $data['patientData'][$u->id]['billing_codes'] = $u->billingCodes($this->month);

                                $data['patientData'][$u->id]['problem1_code'] = $summary->billable_problem1_code;
                                $data['patientData'][$u->id]['problem1'] = $summary->billable_problem1;

                                $data['patientData'][$u->id]['problem2_code'] = $summary->billable_problem2_code;
                                $data['patientData'][$u->id]['problem2'] = $summary->billable_problem2;

                                $data['patientData'][$u->id]['bhi_code'] = optional(optional($summary->billableProblems->first())->pivot)->icd_10_code;
                                $data['patientData'][$u->id]['bhi_problem'] = optional(optional($summary->billableProblems->first())->pivot)->name;
                            }
                        });

        $data['patientData'] = array_key_exists('patientData', $data)
            ? $this->array_orderby($data['patientData'], 'provider', SORT_ASC, 'name', SORT_ASC)
            : null;

        return $data;
    }

    public function incrementInvoiceNo()
    {
        $num = AppConfig::where('config_key', 'billing_invoice_count')
            ->firstOrFail();

        $current = $num->config_value;

        $num->config_value = $current + 1;

        $num->save();

        return $current;
    }

    /**
     * @param $reportName
     *
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     *
     * @return \Spatie\MediaLibrary\Models\Media
     */
    public function makeInvoicePdf($reportName)
    {
        $pdfService = app(PdfService::class);

        \Storage::disk('storage')
            ->makeDirectory('download');

        $path = storage_path("download/${reportName}.pdf");
        $pdf  = $pdfService->createPdfFromView('billing.practice.invoice', $this->getInvoiceData(), [], $path);

        return $this->practice
            ->addMedia($path)
            ->toMediaCollection("invoice_for_{$this->month->toDateString()}");
    }

    /**
     * @param $reportName
     *
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     *
     * @return \Spatie\MediaLibrary\Models\Media
     */
    public function makePatientReportPdf($reportName)
    {
        \Storage::disk('storage')
            ->makeDirectory('download');

        $path = storage_path("/download/${reportName}.pdf");

        $pdfItemized = PDF::loadView('billing.practice.itemized', $this->getItemizedPatientData());
        $pdfItemized->save($path, true);

        return $this->practice
            ->addMedia($path)
            ->toMediaCollection("patient_report_for_{$this->month->toDateString()}");
    }
}

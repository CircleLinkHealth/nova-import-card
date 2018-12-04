<?php

namespace App\Billing\Practices;

use App\AppConfig;
use App\Practice;
use App\Repositories\PatientSummaryEloquentRepository;
use App\User;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Carbon\Carbon;

class PracticeInvoiceGenerator
{
    private $practice;
    private $month;
    private $patients;
    /**
     * @var PatientSummaryEloquentRepository
     */
    private $patientSummaryEloquentRepository;

    /**
     * PracticeInvoiceGenerator constructor.
     *
     * @param Practice $practice
     * @param Carbon $month
     * @param PatientSummaryEloquentRepository $patientSummaryEloquentRepository
     */
    public function __construct(
        Practice $practice,
        Carbon $month
    ) {
        $this->practice = $practice;
        $this->month    = $month->firstOfMonth();
        $this->patientSummaryEloquentRepository = app(PatientSummaryEloquentRepository::class);
    }

    /**
     * @param bool $withItemized
     *
     * @return array
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function generatePdf($withItemized = true)
    {
        $invoiceName = trim($this->practice->name) . '-' . $this->month->toDateString() . '-invoice';

        $pdfInvoice = $this->makeInvoicePdf($invoiceName);

        $data = [
            'invoice_url' => $pdfInvoice->getUrl(),
        ];

        if ($withItemized) {
            $reportName           = trim($this->practice->name) . '-' . $this->month->toDateString() . '-patients';
            $pdfPatientReport = $this->makePatientReportPdf($reportName);

            $data['patient_report_url'] = $pdfPatientReport->getUrl();
        }

        $data['practiceId'] = $this->practice->id;

        return $data;
    }


    /**
     * @param $reportName
     *
     * @return \Spatie\MediaLibrary\Models\Media
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     */
    public function makeInvoicePdf($reportName)
    {
        \Storage::disk('storage')
                ->makeDirectory('download');

        $pdfInvoice = PDF::loadView('billing.practice.invoice', $this->getInvoiceData());
        $pdfInvoice->save(storage_path("download/$reportName.pdf"), true);

        $path = storage_path("download/$reportName.pdf");

        return $this->practice
            ->addMedia($path)
            ->toMediaCollection("invoice_for_{$this->month->toDateString()}");
    }


    /**
     * @param $reportName
     *
     * @return \Spatie\MediaLibrary\Models\Media
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     */
    public function makePatientReportPdf($reportName)
    {
        \Storage::disk('storage')
                ->makeDirectory('download');


        $path = storage_path("/download/$reportName.pdf");

        $pdfItemized = PDF::loadView('billing.practice.itemized', $this->getItemizedPatientData());
        $pdfItemized->save($path, true);

        return $this->practice
            ->addMedia($path)
            ->toMediaCollection("patient_report_for_{$this->month->toDateString()}");
    }


    public function getInvoiceData($chargeableServiceId = null)
    {
        $practiceId = $this->practice->id;

        if ($chargeableServiceId) {
            $billable = User::ofType('participant')
                            ->ofPractice($this->practice->id)
                            ->whereHas('patientSummaries', function ($query) use ($chargeableServiceId) {
                                $query->whereHas('chargeableServices', function ($query) use ($chargeableServiceId) {
                                    $query->where('id', $chargeableServiceId);
                                })
                                      ->where('month_year', $this->month->toDateString())
                                      ->where('approved', '=', true);
                            })
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
            'invoice_amount' => number_format(round((double)$this->practice->clh_pppm * $billable, 2), 2),
            'billable'       => $billable,
        ];
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

    public function getItemizedPatientData()
    {
        $data          = [];
        $data['name']  = $this->practice->display_name;
        $data['month'] = $this->month->toDateString();

        $patients = User::orderBy('first_name', 'asc')
                        ->ofType('participant')
                        ->with([
                            'patientSummaries' => function ($q) {
                                $q->where('month_year', $this->month->toDateString())
                                  ->where('approved', '=', true);
                            },
                            'billingProvider'
                        ])
                        ->whereProgramId($this->practice->id)
                        ->whereHas('patientSummaries', function ($query) {
                            $query->where('month_year', $this->month->toDateString())
                                  ->where('approved', '=', true);
                        })
                        ->chunk(500, function ($patients) use (&$data) {
                            foreach ($patients as $u) {
                                $summary = $u->patientSummaries->first();

                                if (!$this->patientSummaryEloquentRepository->hasBillableProblemsNameAndCode($summary)) {
                                    $summary = $this->patientSummaryEloquentRepository->fillBillableProblemsNameAndCode($summary);
                                    $summary->save();
                                }

                                $data['patientData'][$u->id]['ccm_time']      = round($summary->ccm_time / 60, 2);
                                $data['patientData'][$u->id]['name']          = $u->getFullName();
                                $data['patientData'][$u->id]['dob']           = $u->getBirthDate();
                                $data['patientData'][$u->id]['practice']      = $u->program_id;
                                $data['patientData'][$u->id]['provider']      = $u->getBillingProviderName();
                                $data['patientData'][$u->id]['billing_codes'] = $u->billingCodes($this->month);

                                $data['patientData'][$u->id]['problem1_code'] = $summary->billable_problem1_code;
                                $data['patientData'][$u->id]['problem1']      = $summary->billable_problem1;

                                $data['patientData'][$u->id]['problem2_code'] = $summary->billable_problem2_code;
                                $data['patientData'][$u->id]['problem2']      = $summary->billable_problem2;
                            }
                        });

        $data['patientData'] = array_key_exists('patientData', $data)
            ? $this->array_orderby($data['patientData'], 'provider', SORT_ASC, 'name', SORT_ASC)
            : null;

        return $data;
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
}

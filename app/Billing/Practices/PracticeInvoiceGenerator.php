<?php

namespace App\Billing\Practices;

use App\Activity;
use App\AppConfig;
use App\Practice;
use App\User;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Carbon\Carbon;

class PracticeInvoiceGenerator
{

    private $practice;
    private $month;
    private $patients;

    public function __construct(
        Practice $practice,
        Carbon $month
    ) {

        $this->practice = $practice;
        $this->month    = $month->firstOfMonth();
    }

    public function generatePdf($withItemized = true)
    {
        $invoiceName = trim($this->practice->name) . '-' . $this->month->toDateString() . '-invoice';

        $pdfInvoicePath = $this->makeInvoicePdf($invoiceName);

        $data = [
            'Invoice' => $invoiceName . '.pdf',
        ];

        if ($withItemized) {

            $reportName = trim($this->practice->name) . '-' . $this->month->toDateString() . '-patients';
            $pdfPatientReportPath = $this->makePatientReportPdf($reportName);

            $data['Patient Report'] = $reportName . '.pdf';
        }

        $data['practiceId'] = $this->practice->id;

        return $data;
    }


    public function makeInvoicePdf($reportName) {

        $pdfInvoice = PDF::loadView('billing.practice.invoice', $this->getInvoiceData());
        $pdfInvoice->save(storage_path("download/$reportName.pdf"), true);

        return storage_path("download/$reportName.pdf");
    }



    public function makePatientReportPdf($reportName) {

        $path = storage_path("download/$reportName.pdf");

        $pdfItemized = PDF::loadView('billing.practice.itemized', $this->getItemizedPatientData());
        $pdfItemized->save($path, true);

        return $path;
    }




    public function getInvoiceData()
    {

        $practiceId = $this->practice->id;

        $billable = User::ofType('participant')
                        ->where('program_id', '=', $this->practice->id)
                        ->whereHas('patientSummaries', function ($query) {
                            $query->where('month_year', $this->month->toDateString())
                                  ->where('ccm_time', '>=', 1200)
                                  ->where('approved', '=', true);
                        })
                        ->count() ?? 0;

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

        $patients = User::ofType('participant')
                        ->with([
                            'patientSummaries' => function ($q) {
                                $q->where('month_year', $this->month->toDateString())
                                  ->where('ccm_time', '>=', 1200)
                                  ->where('approved', '=', true);
                            },
                        ])
                        ->where('program_id', '=', $this->practice->id)
                        ->whereHas('patientSummaries', function ($query) {
                            $query->where('month_year', $this->month->toDateString())
                                  ->where('ccm_time', '>=', 1200)
                                  ->where('approved', '=', true);
                        })
                        ->orderBy('updated_at', 'desc')
                        ->get();

        $data          = [];
        $data['name']  = $this->practice->display_name;
        $data['month'] = $this->month->toDateString();

        foreach ($patients as $u) {
            $summary = $u->patientSummaries->first();

            $data['patientData'][$u->id]['ccm_time'] = round($summary->ccm_time / 60, 2);
            $data['patientData'][$u->id]['name']     = $u->fullName;
            $data['patientData'][$u->id]['dob']      = $u->birth_date;
            $data['patientData'][$u->id]['practice'] = $u->program_id;
            $data['patientData'][$u->id]['provider'] = $u->billingProviderName;

            $problem1                                     = isset($summary->problem_1) && $u->ccdProblems
                ? $u->ccdProblems->where('id', $summary->problem_1)->first()
                : null;
            $data['patientData'][$u->id]['problem1_code'] = isset($problem1)
                ? $problem1->icd10Code()
                : null;
            $data['patientData'][$u->id]['problem1']      = $problem1->name ?? null;

            $problem2                                     = isset($summary->problem_2) && $u->ccdProblems
                ? $u->ccdProblems->where('id', $summary->problem_2)->first()
                : null;
            $data['patientData'][$u->id]['problem2_code'] = isset($problem2)
                ? $problem2->icd10Code()
                : null;
            $data['patientData'][$u->id]['problem2']      = $problem2->name ?? null;
        }

        $data['patientData'] = array_key_exists('patientData', $data)
            ? $this->array_orderby($data['patientData'], 'provider', SORT_ASC, 'name', SORT_ASC)
            : null;

        return $data;
    }

    function array_orderby()
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

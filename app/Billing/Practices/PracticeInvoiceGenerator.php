<?php

namespace App\Billing\Practices;


use App\AppConfig;
use App\CLH\CCD\Importer\SnomedToCpmIcdMap;
use App\Patient;
use App\PatientMonthlySummary;
use App\Practice;
use App\User;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Carbon\Carbon;

class PracticeInvoiceGenerator
{

    private $practice;
    private $month;

    public function __construct(
        Practice $practice,
        Carbon $month
    ) {

        $this->practice = $practice;
        $this->month = $month;

    }

    public function generatePdf($withItemized = true)
    {

        $pdfInvoice = PDF::loadView('billing.practice.invoice', $this->getInvoiceData());

        $invoiceName = trim($this->practice->name) . '-' . $this->month->toDateString() . '-invoice';

        $pdfInvoice->save(storage_path("download/$invoiceName.pdf"), true);

        $data = [
            'Invoice' => $invoiceName . '.pdf',
        ];

        if ($withItemized) {

            $pdfItemized = PDF::loadView('billing.practice.itemized', $this->getItemizedPatientData());

            $itemizedName = trim($this->practice->name) . '-' . $this->month->toDateString() . '-patients';

            $pdfItemized->save(storage_path("download/$itemizedName.pdf"), true);

            $data['Patient Report'] = $itemizedName . '.pdf';

        }

        return $data;

    }

    public function getInvoiceData()
    {

        $practiceId = $this->practice->id;
        $month = $this->month;

//        $data['clh_address'] =
//            'CircleLink Health LLC <br/>
//            290 Harbor Drive<br/>
//            Stamford, CT 06902<br/>
//             (203) 858-7206<br/>
//             janstey@circlelinkhealth.com<br/>
//            www.circlelinkhealth.com<br/> ';

        $data['practice'] = $this->practice;
        $data['month'] = $month->toDateString();

        $data['rate'] = $this->practice->clh_pppm;
        $data['invoice_num'] = $this->incrementInvoiceNo();

        $data['billable'] = PatientMonthlySummary
            ::whereHas('patient_info', function ($q) use
            (
                $practiceId,
                $month
            ) {
                $q->whereHas('user', function ($k) use
                (
                    $practiceId,
                    $month
                ) {
                    $k->whereProgramId($practiceId);
                });
            })
            ->where('month_year', $month)
            ->where('approved', 1)
            ->count();

        $data['invoice_amount'] = $this->practice->clh_pppm * $data['billable'];

        return $data;

    }

    public function getItemizedPatientData()
    {

        $practice = $this->practice;
        $date = $this->month->toDateString();

        $patients = Patient
            ::whereHas('patientSummaries', function ($q) use
            (
                $date
            ) {
                $q->where('ccm_time', '>', 1199)
//                    ->where('month_year', Carbon::now()->firstOfMonth()->toDateString())
                    ->where('month_year', $date)
                    ->where('no_of_successful_calls', '>', 0)
                    ->whereNotNull('actor_id')
                    ->where('approved', 1);

            })
            ->whereHas('user', function ($k) use
            (
                $practice

            ) {
                $k->where('program_id', $practice->id);
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        //name, dob, ccm, 2 conditions

        $data = [];
        $data['name'] = $this->practice->name;
        $data['month'] = $date;

        foreach ($patients as $p) {

            $u = $p->user;

            $data['patientData'][$p->user_id]['name'] = $u->fullName;
            $data['patientData'][$p->user_id]['dob'] = $u->birth_date;
            $data['patientData'][$p->user_id]['practice'] = $u->primaryPractice->id;

            $report = $p->patientSummaries()
//                    ->where('month_year', Carbon::now()->firstOfMonth()->toDateString());
                ->where('month_year', '2017-03-01')
                ->first();

            $data['patientData'][$p->user_id]['ccm_time'] = round($report['ccm_time'] / 60, 2);

            //@todo add problem type and code
            $problems = $u->cpmProblems()->take(2)->get();

            $count = 0;
            foreach ($problems as $problem){
                $data['patientData'][$p->user_id]['problems'. $count] = $problem->name . ' (ICD10: ' . SnomedToCpmIcdMap::find($problem->id)->icd_10_code .')';
                $count++;
            }
        }

        return $data;

    }

    public function checkForPendingQAForPractice()
    {

        $practice = $this->practice;
        $date = $this->month->toDateString();

        $count = PatientMonthlySummary::whereHas('patient_info', function ($k) use
        (
            $practice
        ) {

            $k->whereHas('user', function ($q) use
            (
                $practice

            ) {
                $q->where('program_id', $practice->id);
            });

        })//where patient is over 20, and hasn't been accepted or rejected.
        ->where('month_year', $date)
            ->where('ccm_time', '>', 1199)
            ->where('approved', 0)
            ->where('rejected', 0)
            ->count();

        return ($count > 0)
            ? true
            : false;

    }

    public function incrementInvoiceNo(){

        $num = AppConfig::where('config_key', 'billing_invoice_count')->first();

        $current = $num['config_value'];

        $num['config_value'] = $num['config_value'] + 1;
        $num->save();

        return $current;

    }

}
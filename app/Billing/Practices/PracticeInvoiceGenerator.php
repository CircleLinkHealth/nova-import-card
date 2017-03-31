<?php

namespace App\Billing\Practices;


use App\PatientMonthlySummary;
use App\Practice;
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

    public function getData()
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

    public function generatePdf($onlyLink = false)
    {

        $pdf = PDF::loadView('billing.practice.invoice', $this->getData());

        $name = trim($this->practice->name) . '-' . $this->month->toDateString();

        $pdf->save(storage_path("download/$name.pdf"), true);

        if ($onlyLink) {
            return storage_path("download/$name.pdf");
        }

        return [
            'name' => $name,
            'link' => $name. '.pdf'
        ];

    }

}
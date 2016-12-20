<?php

/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 12/12/16
 * Time: 1:08 PM
 */

namespace App\Reports\Sales\Provider;

use App\Practice;
use App\Reports\Sales\Provider\Sections\EnrollmentSummary;
use App\Reports\Sales\Provider\Sections\FinancialSummary;
use App\Reports\Sales\Provider\Sections\PracticeDemographics;
use App\Reports\Sales\Provider\Sections\RangeSummary;
use App\Reports\Sales\SalesReport;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use App\User;
use Carbon\Carbon;

class SalesByProviderReport extends SalesReport
{
    const SECTIONS = [

        RangeSummary::class,
        EnrollmentSummary::class,
        FinancialSummary::class,
        PracticeDemographics::class,

    ];

    private $user;
    private $providerInfo;
    private $sections = [];
    private $practice;
    private $start;
    private $end;

    private $data = [];

    //CircleLink pppm price at account

    public function __construct(
        User $provider,
        $sections,
        Carbon $start,
        Carbon $end
    ) {

        parent::__construct($provider, $start, $end);
        $this->providerInfo = $provider->providerInfo;
        $this->user = $provider;
        $this->start = $start;
        $this->end = $end;
        $this->requestedSections = $sections;

    }

    public function data(){


        foreach ($this->requestedSections as $section){

           $this->data[$section] = (new $section(
               $this->for, $this->start, $this->end
           ))->renderSection();


        }


    }

    public function renderPDF()
    {
        $pdf = PDF::loadView('sales.by-provider.report', ['data' => $this->data]);

        $name = trim($this->user->fullName).'-'.Carbon::now()->toDateString();

        $pdf->save( storage_path("download/$name.pdf"), true );

        return $name.'.pdf';
    }

//    public function formatSalesData(){
//
//
//
//
//        if(in_array('Overall Summary', $this->requestedSections)) {
//
//            $this->sections['']
//
//        }
//
//        in_array('Enrollment Summary', $this->requestedSections)
//            ? $this->generateEnrollmentSummary() : '';
//
//        in_array('Financial Performance', $this->requestedSections)
//            ? $this->generateFinancials() : '';
//
//        in_array('Practice Demographics', $this->requestedSections)
//            ? $this->generatePracticeDemographics() : '';
//
//        $this->data = [
//            'sections' => $this->sections,
//            'range_start' => $this->start->format('l, jS F Y'),
//            'range_end' => $this->end->format('l, jS F Y'),
//            'providerUser' => $this->user
//        ];
//
//    }

//    public function generateEnrollmentSummary(){
//
//        //MAKE TOTAL ENROLLMENTS
//        $id = $this->user->id;
//
//        $enrollmentCumulative = PatientInfo::whereHas('user', function ($q) use ($id) {
//
//            $q->hasBillingProvider($id);
//
//        })
//            ->whereNotNull('ccm_status')
//            ->select(DB::raw('count(ccm_status) as total, ccm_status'))
//            ->groupBy('ccm_status')
//            ->get()
//            ->toArray();
//
//        $this->sections['Enrollment Summary']['enrolled'] = $enrollmentCumulative[0]['total'];
//        $this->sections['Enrollment Summary']['paused'] = $enrollmentCumulative[1]['total'];
//        $this->sections['Enrollment Summary']['withdrawn'] = $enrollmentCumulative[2]['total'];
//
//        for ($i = 0; $i < 4; $i++) {
//
//            $billable = $this->service->billableCountForMonth($this->user, Carbon::parse($this->start)->subMonths($i));
//
//            //if first month, do a month-to-date
//            if ($i == 0) {
//
//                $month = Carbon::parse($this->start)->format('F Y');
//                $this->sections['Enrollment Summary'][$month] = $this->service->enrollmentCountByProvider($this->user,
//                    $this->start, $this->end);
//
//                $this->sections['Enrollment Summary'][$month]['billable'] = $billable;
//
//            } else {
//
//                $iMonthsAgo = Carbon::parse($this->start)->subMonths($i);
//                $start = Carbon::parse($iMonthsAgo)->firstOfMonth();
//                $end = Carbon::parse($iMonthsAgo)->lastOfMonth();
//
//                $month = Carbon::parse($iMonthsAgo)->format('F Y');
//                $this->sections['Enrollment Summary'][$month] = $this->service->enrollmentCountByProvider($this->user,
//                    $start, $end);
//
//                $this->sections['Enrollment Summary'][$month]['billable'] = $billable;
//            }
//
//        }
//
//        return $this->sections['Enrollment Summary'];
//
//    }

//    public function generateFinancials(){
//
//
//        $total =  $this->service->totalBilled($this->user);
//        $this->sections['Financial Performance']['billed_so_far'] = $total;
//
//        $this->sections['Financial Performance']['revenue_so_far'] = '$'.round($total * 40, -2);
//        $this->sections['Financial Performance']['profit_so_far']  = '$'.($total * 40 - $total * $this->clh_pppm);
//
//        for ($i = 1; $i < 5; $i++) {
//
//            $iMonthsAgo = Carbon::parse($this->start)->subMonths($i);
//
//            $start = Carbon::parse($iMonthsAgo)->firstOfMonth();
//
//            $month = Carbon::parse($iMonthsAgo)->format('F Y');
//
//            $billable = $this->service->billableCountForMonth($this->user, $start);
//
//            $this->sections['Financial Performance'][$month]['Billable']
//                = $billable;
//
//            $this->sections['Financial Performance'][$month]['CCM Revenue']
//                = '$' . round($billable * 40, -2);
//
//            $this->sections['Financial Performance'][$month]['CCM Profit']
//                = '$' . ($billable * 40 - $billable * $this->clh_pppm);
//
//        }
//
//        return $this->sections['Financial Performance'];
//
//    }

//    public function generatePracticeDemographics(){
//
//        return $this->sections['Practice Demographics'] = $this->service->getAllUsersAtProvidersPractice($this->user);
//
//    }
}
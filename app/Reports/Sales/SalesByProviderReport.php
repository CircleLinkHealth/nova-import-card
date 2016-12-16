<?php

/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 12/12/16
 * Time: 1:08 PM
 */

namespace App\Reports\Sales;

use App\PatientInfo;
use App\ThirdPartyApiConfig;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesByProviderReport
{

    private $service;
    private $providerInfo;
    private $sections = [];
    private $start;
    private $end;

    private $data = [];

    //CircleLink pppm price at account
    private $clh_pppm = 10;

    public function __construct(User $provider,  $sections, Carbon $st, Carbon $end)
    {

        $this->requestedSections = $sections;

        $this->service = (new ProviderStatsHelper($st, $end));
        $this->providerInfo = $provider->providerInfo;
        $this->user = $provider;
        $this->start = $st;
        $this->end = $end;

    }

    public function getData(){

        $this->formatSalesData();

        return $this->data;

    }

    public function printData(){

        $this->formatSalesData();

        return $this->generatePdf();

    }

    public function formatSalesData(){

        in_array('Overall Summary', $this->requestedSections)
            ? $this->generateOverallSummary() : '';

        in_array('Enrollment Summary', $this->requestedSections)
            ? $this->generateEnrollmentSummary() : '';

        in_array('Financial Performance', $this->requestedSections)
            ? $this->generateFinancials() : '';

        in_array('Practice Demographics', $this->requestedSections)
            ? $this->generatePracticeDemographics() : '';

        $this->data = [
            'sections' => $this->sections,
            'range_start' => $this->start->format('l, jS F Y'),
            'range_end' => $this->end->format('l, jS F Y'),
            'providerUser' => $this->user
        ];

    }

    public function generateEnrollmentSummary(){

        //MAKE TOTAL ENROLLMENTS
        $id = $this->user->id;

        $enrollmentCumulative = PatientInfo::whereHas('user', function ($q) use ($id) {

            $q->hasBillingProvider($id);

        })
            ->whereNotNull('ccm_status')
            ->select(DB::raw('count(ccm_status) as total, ccm_status'))
            ->groupBy('ccm_status')
            ->get()
            ->toArray();

        $this->sections['Enrollment Summary']['enrolled'] = $enrollmentCumulative[0]['total'];
        $this->sections['Enrollment Summary']['paused'] = $enrollmentCumulative[1]['total'];
        $this->sections['Enrollment Summary']['withdrawn'] = $enrollmentCumulative[2]['total'];

        for ($i = 0; $i < 4; $i++) {

            $billable = $this->service->billableCountForMonth($this->user, Carbon::parse($this->start)->subMonths($i));

            //if first month, do a month-to-date
            if ($i == 0) {

                $month = Carbon::parse($this->start)->format('F Y');
                $this->sections['Enrollment Summary'][$month] = $this->service->enrollmentCountByProvider($this->user,
                    $this->start, $this->end);

                $this->sections['Enrollment Summary'][$month]['billable'] = $billable;

            } else {

                $iMonthsAgo = Carbon::parse($this->start)->subMonths($i);
                $start = Carbon::parse($iMonthsAgo)->firstOfMonth();
                $end = Carbon::parse($iMonthsAgo)->lastOfMonth();

                $month = Carbon::parse($iMonthsAgo)->format('F Y');
                $this->sections['Enrollment Summary'][$month] = $this->service->enrollmentCountByProvider($this->user,
                    $start, $end);

                $this->sections['Enrollment Summary'][$month]['billable'] = $billable;
            }

        }

        return $this->sections['Enrollment Summary'];

    }

    public function generateFinancials(){


        $total =  $this->service->totalBilled($this->user);
        $this->sections['Financial Performance']['billed_so_far'] = $total;

        $this->sections['Financial Performance']['revenue_so_far'] = '$'.round($total * 40, -2);
        $this->sections['Financial Performance']['profit_so_far']  = '$'.($total * 40 - $total * $this->clh_pppm);

        for ($i = 1; $i < 5; $i++) {

            $iMonthsAgo = Carbon::parse($this->start)->subMonths($i);

            $start = Carbon::parse($iMonthsAgo)->firstOfMonth();

            $month = Carbon::parse($iMonthsAgo)->format('F Y');

            $billable = $this->service->billableCountForMonth($this->user, $start);

            $this->sections['Financial Performance'][$month]['Billable']
                = $billable;

            $this->sections['Financial Performance'][$month]['CCM Revenue']
                = '$' . round($billable * 40, -2);

            $this->sections['Financial Performance'][$month]['CCM Profit']
                = '$' . ($billable * 40 - $billable * $this->clh_pppm);

        }

        return $this->sections['Financial Performance'];

    }

    public function generatePracticeDemographics(){

        return $this->sections['Practice Demographics'] = $this->service->getAllUsersAtProvidersPractice($this->user);

    }

    public function generateOverallSummary(){

        return $this->sections['Overall Summary'] = [
            'no_of_call_attempts'             => $this->service->callCountForProvider($this->user),
            'no_of_successful_calls'          => $this->service->successfulCallCountForProvider($this->user),
            'total_ccm_time'                  => $this->service->totalCCMTime($this->user),
            'no_of_biometric_entries'         => $this->service->numberOfBiometricsRecorded($this->user),
            'no_of_forwarded_notes'           => $this->service->noteStats($this->user),
            'no_of_forwarded_emergency_notes' => $this->service->emergencyNotesCount($this->user),
            'link_to_notes_listing'           => $this->service->linkToProviderNotes($this->user)
        ];

    }

    public function generatePdf(){

        $pdf = PDF::loadView('sales.by-provider.report', ['data' => $this->data]);

        $name = trim($this->user->fullName).'-'.Carbon::now()->toDateString();

        $pdf->save( storage_path("download/$name.pdf"), true );

        return $name.'.pdf';

    }

}
<?php

/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 12/12/16
 * Time: 1:08 PM
 */

namespace App\Reports\Sales;

use App\ThirdPartyApiConfig;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use App\User;
use Carbon\Carbon;

class SalesByProviderReport
{

    private $service;
    private $providerInfo;
    private $sections = [];
    private $start;
    private $end;

    /*
        Overall Summary
        Financial Performance

        CCM Revenue to date: ~$2,200
        CCM Profit to date: ~$770
        Patients billed to date: 56

        [# of Leads/Admins at customer]

        [# of Providers + # of Specialists] are Providers,
        [# of RNs] are RNs [# of office staff]
        [# of MAs] are MAs

     */

    public function __construct(User $provider, Carbon $st, Carbon $end)
    {

        $this->service = (new ProviderStatsHelper($st, $end));
        $this->providerInfo = $provider->providerInfo;
        $this->user = $provider;

        $this->start = $st;
        $this->end = $end;

    }

    public function handle(){

        $this->formatSalesData();

        return $this->sections;

    }

    public function formatSalesData(){

        $this->sections['Overall Summary'] = [
            'no_of_call_attempts' =>             $this->service->callCountForProvider($this->user),
            'no_of_successful_calls' =>          $this->service->successfulCallCountForProvider($this->user),
            'total_ccm_time' =>                  $this->service->totalCCMTime($this->user),
            'no_of_biometric_entries' =>         $this->service->numberOfBiometricsRecorded($this->user),
            'no_of_forwarded_notes' =>           $this->service->noteStats($this->user),
            'no_of_forwarded_emergency_notes' => $this->service->emergencyNotesCount($this->user),
            'link_to_notes_listing' =>           $this->service->linkToProviderNotes($this->user)
        ];

        $this->sections['Enrollment Summary'] = $this->service->enrollmentCountByProvider($this->user, $this->start, $this->end);

        $this->sections['Financial Performance'] = $this->service->billableCount($this->user, $this->start);

        $this->data = [
            'sections' => $this->sections,
            'range'

        ];


    }

    public function generatePdf(){

        $pdf = PDF::loadView('sales.by-location.make', ['data' => $this->data]);

        $name = trim($this->program->name).'-'.Carbon::now()->toDateString();

        $pdf->save( storage_path("download/$name.pdf"), true );

        return $name.'.pdf';

    }

}
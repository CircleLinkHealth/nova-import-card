<?php
/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 12/12/16
 * Time: 1:08 PM
 */

namespace App\Billing;

use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use App\User;
use Carbon\Carbon;

class SalesByProviderReport
{

    private $service;
    private $providerInfo;

    /*
        [# of call attempts at org]
        [# of successful calls]
        [CCM time at org for week]
        [# of biometric inputs]
        [# forwarded notes at organization]

        You can see a list of forwarded notes here

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

        $this->service = (new ProviderStatsHelper($provider, $st, $end));
        $this->providerInfo = $provider->providerInfo;

    }

    public function handle(){

        $data = [];

    }

    public function formatSalesData(){

        $this->data = [
            'provider_name' => $this->providerInfo->user->fullName,

        ];


    }

    public function generatePdf(){

        $pdf = PDF::loadView('sales.by-location.make', ['data' => $this->data]);

        $name = trim($this->program->name).'-'.Carbon::now()->toDateString();

        $pdf->save( storage_path("download/$name.pdf"), true );

        return $name.'.pdf';

    }

}
<?php
/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 12/12/16
 * Time: 1:08 PM
 */

namespace App\Billing;


use App\Location;
use App\Practice;
use Carbon\Carbon;

class SalesReport
{

    private $service;

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

    public function __construct(Practice $practice, Carbon $st, Carbon $end)
    {

        $this->service = (new PracticeStatsHelper($practice, $st, $end));

    }

    public function handle(){

        $data = [];

    }

    public function generatePdf(){

    }

}
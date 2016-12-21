<?php

/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 12/12/16
 * Time: 1:08 PM
 */

namespace App\Reports\Sales\Provider;

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

        'Overall Summary' => RangeSummary::class,
        'Enrollment Summary' => EnrollmentSummary::class,
        'Financial Performance' => FinancialSummary::class,
        'Practice Demographics'=> PracticeDemographics::class

    ];

    private $user;
    private $providerInfo;
    private $sections = [];
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

    public function data($defaultSections = false){

        if($defaultSections){
            $this->requestedSections = self::SECTIONS;
        }

        foreach ($this->requestedSections as $key=> $section){

           $this->data[$key] = (new $section(
               $this->for, $this->start, $this->end
           ))->renderSection();

        }

        return $this->data;

    }

    public function renderPDF()
    {
        $pdf = PDF::loadView('sales.by-provider.report', ['data' => $this->data]);

        $name = trim($this->user->fullName).'-'.Carbon::now()->toDateString();

        $pdf->save( storage_path("download/$name.pdf"), true );

        return $name.'.pdf';
    }

}
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
use App\User;
use Carbon\Carbon;

class SalesByProviderReport extends SalesReport
{
    const SECTIONS = [

        'Overall Summary'       => RangeSummary::class,
        'Enrollment Summary'    => EnrollmentSummary::class,
        'Financial Performance' => FinancialSummary::class,
        'Practice Demographics' => PracticeDemographics::class,

    ];

    private $user;
    private $providerInfo;

    public function __construct(
        User $provider,
        $sections,
        Carbon $start,
        Carbon $end
    ) {

        parent::__construct($provider, $sections, $start, $end);
        $this->providerInfo = $provider->providerInfo;
        $this->user = $provider;
        $this->start = $start;
        $this->end = $end;

    }

    public function renderPDF(
        $name,
        $view = 'sales.by-provider.create'
    ) {
        $this->data();

        return parent::renderPDF($name, $view);
    }

    public function data($defaultSections = false)
    {

        if ($defaultSections) {

            return $this->requestedSections = self::SECTIONS;

        } else {

            return parent::data(false);

        }

    }

    public function renderView($view = 'sales.by-provider.create')
    {
        return parent::renderView($view);
    }

}
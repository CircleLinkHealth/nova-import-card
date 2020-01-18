<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Reports\Sales\Provider;

use App\Reports\Sales\Provider\Sections\EnrollmentSummary;
use App\Reports\Sales\Provider\Sections\FinancialSummary;
use App\Reports\Sales\Provider\Sections\PracticeDemographics;
use App\Reports\Sales\Provider\Sections\RangeSummary;
use App\Reports\Sales\SalesReport;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;

class SalesByProviderReport extends SalesReport
{
    const SECTIONS = [
        'Overall Summary'       => RangeSummary::class,
        'Enrollment Summary'    => EnrollmentSummary::class,
        'Financial Performance' => FinancialSummary::class,
        'Practice Demographics' => PracticeDemographics::class,
    ];
    private $providerInfo;

    private $user;

    public function __construct(
        User $provider,
        $sections,
        Carbon $start,
        Carbon $end
    ) {
        parent::__construct($provider, $sections, $start, $end);
        $this->providerInfo = $provider->providerInfo;
        $this->user         = $provider;
        $this->start        = $start;
        $this->end          = $end;
    }

    public function data($defaultSections = false)
    {
        if ($defaultSections) {
            $this->requestedSections = self::SECTIONS;
        }

        return parent::data();
    }

    public function renderPDF(
        $name,
        $view = 'sales.by-provider.create'
    ) {
        $this->data();

        return parent::renderPDF($name, $view);
    }

    public function renderView($view = 'sales.by-provider.create')
    {
        return parent::renderView($view);
    }
}

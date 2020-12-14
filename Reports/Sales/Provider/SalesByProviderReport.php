<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Reports\Sales\Provider;

use Carbon\Carbon;
use CircleLinkHealth\CpmAdmin\Reports\Sales\Provider\Sections\EnrollmentSummary;
use CircleLinkHealth\CpmAdmin\Reports\Sales\Provider\Sections\FinancialSummary;
use CircleLinkHealth\CpmAdmin\Reports\Sales\Provider\Sections\PracticeDemographics;
use CircleLinkHealth\CpmAdmin\Reports\Sales\Provider\Sections\RangeSummary;
use CircleLinkHealth\CpmAdmin\Reports\Sales\SalesReport;
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
        $view = 'cpm-admin::sales.by-provider.create'
    ) {
        $this->data();

        return parent::renderPDF($name, $view);
    }

    public function renderView($view = 'cpm-admin::sales.by-provider.create')
    {
        return parent::renderView($view);
    }
}

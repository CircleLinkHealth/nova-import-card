<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Reports\Sales\Practice;

use Carbon\Carbon;
use CircleLinkHealth\CpmAdmin\Reports\Sales\Practice\Sections\EnrollmentSummary;
use CircleLinkHealth\CpmAdmin\Reports\Sales\Practice\Sections\FinancialSummary;
use CircleLinkHealth\CpmAdmin\Reports\Sales\Practice\Sections\PracticeDemographics;
use CircleLinkHealth\CpmAdmin\Reports\Sales\Practice\Sections\RangeSummary;
use CircleLinkHealth\CpmAdmin\Reports\Sales\SalesReport;

class SalesByPracticeReport extends SalesReport
{
    const SECTIONS = [
        'Overall Summary'       => RangeSummary::class,
        'Enrollment Summary'    => EnrollmentSummary::class,
        'Financial Performance' => FinancialSummary::class,
        'Practice Demographics' => PracticeDemographics::class,
    ];

    public function __construct(
        $for,
        $sections,
        Carbon $start,
        Carbon $end
    ) {
        parent::__construct($for, $sections, $start, $end);
    }

    public function data($defaultSections = false)
    {
        if ($defaultSections) {
            $this->requestedSections = self::SECTIONS;

            return parent::data();
        }

        return parent::data();
    }

    public function renderPDF(
        $name,
        $view = 'cpm-admin::sales.by-practice.create'
    ) {
        $this->data();

        return parent::renderPDF($name, $view);
    }

    public function renderView($view = 'cpm-admin::sales.by-practice.create')
    {
        return parent::renderView($view); // TODO: Change the autogenerated stub
    }
}
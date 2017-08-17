<?php
/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 12/19/16
 * Time: 5:53 PM
 */

namespace App\Reports\Sales\Practice;

use App\Reports\Sales\Practice\Sections\EnrollmentSummary;
use App\Reports\Sales\Practice\Sections\FinancialSummary;
use App\Reports\Sales\Practice\Sections\PracticeDemographics;
use App\Reports\Sales\Practice\Sections\RangeSummary;
use App\Reports\Sales\SalesReport;
use Carbon\Carbon;

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

    public function renderPDF(
        $name,
        $view = 'sales.by-practice.create'
    ) {
        $this->data();

        return parent::renderPDF($name, $view);
    }

    public function data($defaultSections = false)
    {
        if ($defaultSections) {
            $this->requestedSections = self::SECTIONS;
            return parent::data();
        } else {
            return parent::data();
        }
    }

    public function renderView($view = 'sales.by-practice.create')
    {
        return parent::renderView($view); // TODO: Change the autogenerated stub
    }

}
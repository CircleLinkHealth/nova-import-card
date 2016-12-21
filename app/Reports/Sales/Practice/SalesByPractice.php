<?php
/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 12/19/16
 * Time: 5:53 PM
 */

namespace App\Reports\Sales;

use App\Reports\Sales\Practice\Sections\EnrollmentSummary;
use App\Reports\Sales\Practice\Sections\FinancialSummary;
use App\Reports\Sales\Practice\Sections\PracticeDemographics;
use App\Reports\Sales\Practice\Sections\RangeSummary;

use Carbon\Carbon;

class SalesByPractice extends SalesReport
{

    private $practice;

    const SECTIONS = [
        RangeSummary::class,
        EnrollmentSummary::class,
        FinancialSummary::class,
        PracticeDemographics::class,
    ];

    public function __construct(
        $for,
        $sections,
        Carbon $start,
        Carbon $end
    ) {
        parent::__construct($for, $sections, $start, $end);
        $this->practice = $for;
    }

    public function data($defaultSections = false)
    {

        if ($defaultSections) {

            return $this->requestedSections = self::SECTIONS;

        } else {

            return parent::data();

        }

    }

    public function renderPDF(
        $name,
        $view,
        $data
    ) {
        return parent::renderPDF($name, $view, $data); // TODO: Change the autogenerated stub
    }

}
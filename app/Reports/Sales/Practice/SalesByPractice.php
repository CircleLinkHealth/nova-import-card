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

    const SECTIONS = [
        RangeSummary::class,
        EnrollmentSummary::class,
        FinancialSummary::class,
        PracticeDemographics::class,
    ];

    public function __construct(
        $for,
        Carbon $start,
        Carbon $end
    ) {
        parent::__construct($for, $start, $end);
    }


    public function generateData()
    {



    }

    public function renderPDF()
    {

    }

}
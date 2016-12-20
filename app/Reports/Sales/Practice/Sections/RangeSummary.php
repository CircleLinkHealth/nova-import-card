<?php namespace App\Reports\Sales\Practice\Sections;

use App\Practice;
use App\Reports\Sales\SalesReportSection;
use Carbon\Carbon;

class RangeSummary extends SalesReportSection
{

    private $practice;

    public function __construct(
        Practice $practice,
        Carbon $start,
        Carbon $end
    ) {
        parent::__construct($practice, $start, $end);
    }

    public function renderSection()
    {

    }
}
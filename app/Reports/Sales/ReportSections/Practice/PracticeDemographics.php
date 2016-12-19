<?php
use App\Practice;
use Carbon\Carbon;

/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 12/19/16
 * Time: 5:20 PM
 */
class PracticeDemographics extends SalesReportSection
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
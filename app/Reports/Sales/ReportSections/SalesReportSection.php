<?php
use Carbon\Carbon;

/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 12/19/16
 * Time: 5:25 PM
 */
abstract class SalesReportSection
{

    private $startRange;
    private $endRange;
    private $for;

    public function __construct($for, Carbon $start, Carbon $end)
    {

        $this->for = $for;
        $this->startRange = $start;
        $this->endRange = $end;

    }

    public function renderSection(){



    }

}
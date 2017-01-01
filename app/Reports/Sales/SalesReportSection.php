<?php namespace App\Reports\Sales;

use Carbon\Carbon;

/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 12/19/16
 * Time: 5:25 PM
 */
abstract class SalesReportSection
{

    protected $start;
    protected $end;
    protected $for;

    protected $data;

    public function __construct(
        $for,
        Carbon $start,
        Carbon $end
    ) {

        $this->for = $for;
        $this->start = $start;
        $this->end = $end;

    }

    public function renderSection()
    {


    }

}
<?php namespace App\Reports\Sales;

use App\Contracts\Reports\Section;
use Carbon\Carbon;

/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 12/19/16
 * Time: 5:25 PM
 */
abstract class SalesReportSection implements Section
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

    abstract function render();
}
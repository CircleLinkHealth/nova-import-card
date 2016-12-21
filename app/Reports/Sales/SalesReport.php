<?php
/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 12/19/16
 * Time: 5:16 PM
 */

namespace App\Reports\Sales;

use Carbon\Carbon;

abstract class SalesReport
{

    protected $startRange;
    protected $endRange;
    protected $for;
    protected $requestedSections;

    public function __construct($for, Carbon $start, Carbon $end)
    {

        $this->for = $for;
        $this->startRange = $start;
        $this->endRange = $end;

    }

    public function data(){

    }

    public function renderPDF(){

    }

}
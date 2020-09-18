<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Services\Reports\Sales;

use App\Contracts\Reports\Section;
use Carbon\Carbon;

/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 12/19/16
 * Time: 5:25 PM.
 */
abstract class SalesReportSection implements Section
{
    protected $data;
    protected $end;
    protected $for;
    protected $start;

    public function __construct(
        $for,
        Carbon $start,
        Carbon $end
    ) {
        $this->for   = $for;
        $this->start = $start;
        $this->end   = $end;
    }

    abstract public function render();
}

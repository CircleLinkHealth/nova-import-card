<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Billing\NurseInvoices;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Nurse;

/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 1/10/17
 * Time: 3:06 PM.
 */
abstract class NurseInvoice
{
    protected $data;
    protected $end;

    //initializations
    protected $nurse;

    protected $payable;
    protected $start;

    public function __construct(Nurse $nurse, Carbon $start, Carbon $end)
    {
        $this->nurse = $nurse;
        $this->start = $start;
        $this->end   = $end;
    }
}

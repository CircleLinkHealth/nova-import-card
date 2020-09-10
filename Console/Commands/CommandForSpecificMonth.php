<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class CommandForSpecificMonth extends Command
{
    public function month(): Carbon
    {
        /** @var Carbon */
        $month = ! empty($this->argument('month')) ? Carbon::parse($this->argument('month')) : Carbon::now()->startOfMonth();

        if ($month->notEqualTo($month->copy()->startOfMonth())) {
            $month->startOfMonth();
        }
    }
}

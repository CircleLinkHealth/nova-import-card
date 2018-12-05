<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

interface CallHandler
{
    //calculate how much time to wait before next call
    public function getComplexPatientOffset($ccmTime, $week);

    //calculate how much time to wait before next call
    public function getPatientOffset($ccmTime, $week);

    //exec function
    public function handle();
}

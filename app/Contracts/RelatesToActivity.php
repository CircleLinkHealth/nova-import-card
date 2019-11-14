<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

interface RelatesToActivity
{
    /**
     * Return a call object.
     *
     * @return mixed
     */
    public function getActivities();

    public function markActivityAsDone();
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

trait ActivityRelatable
{
    public function markActivityAsDone()
    {
        $toUpdate = [
            'asap'   => false,
            'status' => 'done',
        ];

        $activities = $this->getActivities();
        $activities->update($toUpdate);
    }
}

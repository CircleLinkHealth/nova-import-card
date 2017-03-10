<?php

namespace App\Observers;

use App\CarePlan;

class CarePlanObserver
{
    /**
     * Listen to the User created event.
     *
     * @param CarePlan $carePlan
     *
     */
    public function creating(CarePlan $carePlan)
    {
        if ($carePlan->patient->primaryPractice->auto_approve_careplans) {
            $carePlan->status = 'provider_approved';
        }
    }

}
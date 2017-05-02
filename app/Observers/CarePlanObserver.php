<?php

namespace App\Observers;

use App\CarePlan;

class CarePlanObserver
{
    /**
     * Listen to the CarePlan saving event.
     *
     * @param CarePlan $carePlan
     *
     */
    public function saving(CarePlan $carePlan)
    {
        if ($carePlan->patient->primaryPractice->settings()->first()->auto_approve_careplans) {
            $carePlan->status = 'provider_approved';
        }
    }

}
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
        if (!array_key_exists('care_plan_template_id', $carePlan->getAttributes())) {
            $carePlan->care_plan_template_id = getDefaultCarePlanTemplate()->id;
        }
    }

}
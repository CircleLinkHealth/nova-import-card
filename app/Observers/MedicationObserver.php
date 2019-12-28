<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\MedicationGroupsMap;
use App\Models\CCD\Medication;

class MedicationObserver
{
    /**
     * Listen to the Medication creating event.
     *
     * @param Medication $medication
     */
    public function creating(Medication $medication)
    {
        if ( ! $medication->medication_group_id && $medication->name) {
            $medication->medication_group_id = MedicationGroupsMap::getGroup($medication->name);
        }
    }
}

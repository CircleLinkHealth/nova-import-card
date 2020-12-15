<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use CircleLinkHealth\SharedModels\Entities\Medication;
use CircleLinkHealth\SharedModels\Entities\MedicationGroupsMap;

class MedicationObserver
{
    /**
     * Listen to the Medication creating event.
     */
    public function creating(Medication $medication)
    {
        if ( ! $medication->medication_group_id && ($medication->name || $medication->sig)) {
            $medication->medication_group_id = MedicationGroupsMap::getGroup($medication->name) ?? MedicationGroupsMap::getGroup($medication->sig);
        }
    }
}

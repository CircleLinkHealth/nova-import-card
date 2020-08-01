<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use CircleLinkHealth\SharedModels\Entities\CpmMedicationGroup;
use CircleLinkHealth\SharedModels\Entities\Medication;

class CpmMedicationGroupRepository
{
    public function count()
    {
        return $this->model()->count();
    }

    public function group($id)
    {
        $group = $this->model()->find($id);
        if ($group) {
            return $this->setupGroupMedication($group);
        }

        return null;
    }

    public function groups()
    {
        $groups = $this->model()->paginate();

        return $groups->getCollection()->transform([$this, 'setupGroupMedicationCount']);
    }

    public function model()
    {
        return app(CpmMedicationGroup::class);
    }

    public function patientGroups($userId)
    {
        return array_values(Medication::where([
            'patient_id' => $userId,
        ])->groupBy('medication_group_id')->with(['cpmMedicationGroup'])->get()->map(function ($m) {
            return $m->cpmMedicationGroup;
        })->filter()->toArray());
    }

    public function setupGroupMedication($group)
    {
        $group['medications'] = $group->medications()->paginate();

        return $group;
    }

    public function setupGroupMedicationCount($group)
    {
        $group['medications'] = $group->medications()->count();

        return $group;
    }
}

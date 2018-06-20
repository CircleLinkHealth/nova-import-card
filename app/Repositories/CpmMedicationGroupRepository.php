<?php

namespace App\Repositories;

use App\Models\CCD\Medication;
use App\Models\CPM\CpmMedicationGroup;

class CpmMedicationGroupRepository
{
    public function model()
    {
        return new CpmMedicationGroup();
    }

    public function count()
    {
        return $this->model()->count();
    }

    function setupGroupMedicationCount($group)
    {
        $group['medications'] = $group->medications->count();

        return $group;
    }

    function setupGroupMedication($group)
    {
        $group['medications'] = $group->medications->paginate();

        return $group;
    }

    public function groups()
    {
        $groups = $this->model()
                       ->paginate();

        return $groups->getCollection()
                      ->transform([$this, 'setupGroupMedicationCount']);
    }

    public function group($id)
    {
        $group = $this->model()
                      ->with('medications')
                      ->find($id);

        if ($group) {
            return $this->setupGroupMedication($group);
        }

        return null;
    }

    public function patientGroups($userId)
    {
        return Medication::where([
            'patient_id' => $userId,
        ])
                         ->select('medication_group_id')
                         ->groupBy('medication_group_id')
                         ->with(['cpmMedicationGroup'])
                         ->get()
                         ->map(function ($m) {
                             return $m->cpmMedicationGroup;
                         })
                         ->unique()
                         ->toArray();
    }
}
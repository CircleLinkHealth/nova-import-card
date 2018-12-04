<?php

namespace App\Repositories;

use App\Models\CCD\Medication;
use App\Models\CPM\CpmMedicationGroup;

class CpmMedicationGroupRepository
{
    public function model()
    {
        return app(CpmMedicationGroup::class);
    }

    public function count()
    {
        return $this->model()->count();
    }

    public function setupGroupMedicationCount($group)
    {
        $group['medications'] = $group->medications()->count();

        return $group;
    }

    public function setupGroupMedication($group)
    {
        $group['medications'] = $group->medications()->paginate();

        return $group;
    }

    public function groups()
    {
        $groups = $this->model()->paginate();

        return $groups->getCollection()->transform([$this, 'setupGroupMedicationCount']);
    }

    public function group($id)
    {
        $group = $this->model()->find($id);
        if ($group) {
            return $this->setupGroupMedication($group);
        } else {
            return null;
        }
    }

    public function patientGroups($userId)
    {
        return array_values(Medication::where([
            'patient_id' => $userId,
        ])->groupBy('medication_group_id')->with(['cpmMedicationGroup'])->get()->map(function ($m) {
            return $m->cpmMedicationGroup;
        })->filter()->toArray());
    }
}

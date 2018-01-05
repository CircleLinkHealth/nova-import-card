<?php

namespace App\Repositories;

use App\User;
use App\Patient;
use App\Models\CPM\CpmMedicationGroup;

class CpmMedicationGroupRepository
{
    public function model()
    {
        return app(CpmMedicationGroup::class);
    }

    public function count() {
        return $this->model()->count();
    }

    function setupGroupMedicationCount($group) {
        $group['medications'] = $group->medications()->count();
        return $group;
    }

    function setupGroupMedication($group) {
        $group['medications'] = $group->medications()->paginate();
        return $group;
    }

    public function groups() {
        $groups = $this->model()->paginate();
        return $groups->getCollection()->transform([$this, 'setupGroupMedicationCount']);
    }
    
    public function group($id) {
        $group = $this->model()->find($id);
        if ($group) {
            return $this->setupGroupMedication($group);
        }
        else {
            return null;
        }
    }
}
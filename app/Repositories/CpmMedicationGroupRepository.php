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
}
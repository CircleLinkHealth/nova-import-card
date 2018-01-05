<?php

namespace App\Repositories;

use App\User;
use App\Patient;
use App\Models\CCD\Medication;

class CpmMedicationRepository
{
    public function model()
    {
        return app(Medication::class);
    }

    public function count() {
        return $this->model()->count();
    }
}
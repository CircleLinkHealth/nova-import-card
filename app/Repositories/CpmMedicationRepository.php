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

    public function search($terms) {
        $query = $this->model();
        if (is_array($terms)) {
            $i = 0;
            foreach ($terms as $term) {
                if ($i == 0) $query = $query->where('name', 'LIKE', '%'.$term.'%');
                else $query = $query->orWhere('name', 'LIKE', '%'.$term.'%');
                $i++;
            }
        }
        else $query = $query->orWhere('name', 'LIKE', '%'.$terms.'%');
        return $query->groupBy('name')->get();
    }
}
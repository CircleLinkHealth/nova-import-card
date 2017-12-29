<?php

namespace App\Repositories;

use App\Models\CCD\Allergy;

class CcdAllergyRepository
{

    public function model()
    {
        return app(Allergy::class);
    }
    
    public function count() {
        return $this->model->select('allergen_name', DB::raw('count(*) as total'))->groupBy('allergen_name')->pluck('total')->count();
    }
    
    public function patientIds($name) {
        return $this->model()->where(['allergen_name' => $name ])->distinct(['patient_id'])->get(['patient_id']);
    }

    public function allergies() {
        return $this->model()->groupBy('allergen_name')->paginate(30);
    }
}
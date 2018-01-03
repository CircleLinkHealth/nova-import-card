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
        return $this->model()->select('allergen_name', DB::raw('count(*) as total'))->groupBy('allergen_name')->pluck('total')->count();
    }
    
    public function patientIds($name) {
        return $this->model()->where(['allergen_name' => $name ])->distinct(['patient_id'])->get(['patient_id']);
    }

    public function allergies() {
        return $this->model()->groupBy('allergen_name')->paginate(30);
    }
    
    public function patientAllergies($userId) {
        return $this->model()->where([ 'patient_id' => $userId ])->get();
    }

    public function searchAllergies($terms) {
        $query = $this->model();
        if (is_array($terms)) {
            $i = 0;
            foreach ($terms as $term) {
                if ($i == 0) $query = $query->where('allergen_name', 'LIKE', '%'.$term.'%');
                else $query = $query->orWhere('allergen_name', 'LIKE', '%'.$term.'%');
                $i++;
            }
        }
        else $query = $query->orWhere('allergen_name', 'LIKE', '%'.$terms.'%');
        return $query->groupBy('allergen_name')->get();
    }
}
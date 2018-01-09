<?php

namespace App\Services\CCD;

use App\User;
use App\Repositories\UserRepositoryEloquent;
use App\Repositories\CcdAllergyRepository;

class CcdAllergyService
{
    private $allergyRepo;
    private $userRepo;

    public function __construct(CcdAllergyRepository $allergyRepo, UserRepositoryEloquent $userRepo) {
        $this->allergyRepo = $allergyRepo;
        $this->userRepo = $userRepo;
    }

    public function repo() {
        return $this->allergyRepo;
    }
    
    function setupAllergy($a) {
        $allergy = [
            'id'    => $a->id,
            'name'  => $a->allergen_name,
            'created_at' => $a->created_at->format('c'),
            'updated_at' => $a->updated_at->format('c'),
            'patients' => $this->repo()->patientIds($a->allergen_name)->map(function ($patient) {
                return $patient->patient_id;
            })
        ];
        return $allergy;
    }

    public function allergies() {
        $allergies = $this->repo()->allergies();
        $allergies->getCollection()->transform(function ($value) {
            return $this->setupAllergy($value);
        });
        return $allergies;
    }

    public function searchAllergies($terms) {
        return $this->repo()->searchAllergies($terms)->map(function ($a) {
            return $this->setupAllergy($a);
        });;
    }
    
    public function allergy($id) {
        $allergy = $this->repo()->model()->find($id);
        if ($allergy) return $this->setupAllergy($allergy);
        else return null;
    }
    
    public function patientAllergies($userId) {
        return $this->repo()->patientAllergies($userId)->map(function ($a) {
            return [
                'id'    => $a->id,
                'name'  => $a->allergen_name,
                'created_at' => $a->created_at->format('c'),
                'updated_at' => $a->updated_at->format('c')
            ];
        });
    }
}

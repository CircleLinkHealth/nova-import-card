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
    
    public function allergy($id) {
        $allergy = $this->repo()->model()->find($id);
        if ($allergy) return $this->setupAllergy($allergy);
        else return null;
    }
}

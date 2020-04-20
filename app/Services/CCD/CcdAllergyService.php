<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\CCD;

use App\Repositories\CcdAllergyRepository;
use CircleLinkHealth\Customer\Entities\User;

class CcdAllergyService
{
    private $allergyRepo;

    public function __construct(CcdAllergyRepository $allergyRepo)
    {
        $this->allergyRepo = $allergyRepo;
    }

    public function addPatientAllergy($userId, $name)
    {
        if ( ! $this->allergyRepo->patientAllergyExists($userId, $name)) {
            return $this->setupAllergy($this->allergyRepo->addPatientAllergy($userId, $name));
        }

        return null;
    }

    public function allergies()
    {
        $allergies = $this->allergyRepo->allergies();
        $allergies->getCollection()->transform(function ($value) {
            return $this->setupAllergy($value);
        });

        return $allergies;
    }

    public function allergy($id)
    {
        $allergy = $this->allergyRepo->model()->find($id);
        if ($allergy) {
            return $this->setupAllergy($allergy);
        }

        return null;
    }

    public function deletePatientAllergy($userId, $allergyId)
    {
        return $this->allergyRepo->deletePatientAllergy($userId, $allergyId);
    }

    public function patientAllergies($userId)
    {
        $relQuery = [
            'ccdAllergies' => function ($q) {
                return $q->distinct('allergen_name');
            },
        ];

        if (is_a($userId, User::class)) {
            $user = $userId;

            $user->loadMissing($relQuery);
        } else {
            $user = User::with($relQuery)->findOrFail($userId);
        }

        return $user->ccdAllergies
            ->map(function ($a) {
                return [
                    'id'         => $a->id,
                    'name'       => $a->allergen_name,
                    'created_at' => $a->created_at->format('c'),
                    'updated_at' => $a->updated_at->format('c'),
                ];
            });
    }

    public function searchAllergies($terms)
    {
        return $this->allergyRepo->searchAllergies($terms)->map(function ($a) {
            return $this->setupAllergy($a);
        });
    }

    public function setupAllergy($a)
    {
        if ($a) {
            return [
                'id'         => $a->id,
                'name'       => $a->allergen_name,
                'created_at' => $a->created_at->format('c'),
                'updated_at' => $a->updated_at->format('c'),
            ];
        }

        return null;
    }
}

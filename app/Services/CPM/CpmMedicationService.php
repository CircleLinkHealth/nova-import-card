<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\CPM;

use App\CLH\Repositories\UserRepository;
use App\Models\CCD\Medication;
use App\Repositories\CpmMedicationRepository;

class CpmMedicationService
{
    private $medicationRepo;
    private $userRepo;

    public function __construct(CpmMedicationRepository $medicationRepo, UserRepository $userRepo)
    {
        $this->medicationRepo = $medicationRepo;
        $this->userRepo       = $userRepo;
    }

    public function editPatientMedication(Medication $medication)
    {
        if ( ! $medication) {
            throw new Exception('invalid parameters');
        }
        if ( ! $medication->id) {
            throw new Exception('parameter "id" is important');
        }
        if ( ! $medication->patient_id) {
            throw new Exception('parameter "patient_id" is important');
        }
        if ( ! $this->userRepo->exists($medication->patient_id)) {
            throw new Exception('no user exists with id "'.$medication->patient_id.'"');
        }
        if ( ! $this->repo()->exists($medication->id)) {
            throw new Exception('no medication exists with id "'.$medication->id.'"');
        }
        if ($this->repo()->model()->find($medication->id)->patient_id != $medication->patient_id) {
            throw new Exception('user with id "'.$medication->patient_id.'" does own medication with id "'.$medication->id.'"');
        }

        return $this->repo()->editPatientMedication($medication);
    }

    public function medications()
    {
        return $this->repo()->model()->paginate();
    }

    public function repo()
    {
        return $this->medicationRepo;
    }

    public function search($terms)
    {
        return $this->repo()->search($terms);
    }
}

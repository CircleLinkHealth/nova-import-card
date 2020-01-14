<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\CPM;

use App\Repositories\CpmMedicationRepository;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Medication;

class CpmMedicationService
{
    private $medicationRepo;

    public function __construct(CpmMedicationRepository $medicationRepo)
    {
        $this->medicationRepo = $medicationRepo;
    }

    public function editPatientMedication(Medication $medication)
    {
        if ( ! $medication) {
            throw new \Exception('invalid parameters');
        }
        if ( ! $medication->id) {
            throw new \Exception('parameter "id" is important');
        }
        if ( ! $medication->patient_id) {
            throw new \Exception('parameter "patient_id" is important');
        }
        if ( ! User::exists($medication->patient_id)) {
            throw new \Exception('no user exists with id "'.$medication->patient_id.'"');
        }
        if ( ! $this->repo()->exists($medication->id)) {
            throw new \Exception('no medication exists with id "'.$medication->id.'"');
        }
        if (Medication::find($medication->id)->patient_id != $medication->patient_id) {
            throw new \Exception('user with id "'.$medication->patient_id.'" does own medication with id "'.$medication->id.'"');
        }

        return $this->repo()->editPatientMedication($medication);
    }

    public function medications()
    {
        return Medication::orderBy('name')->paginate();
    }

    public function patientMedicationPaginated(int $userId)
    {
        return Medication::where([
            'patient_id' => $userId,
        ])->orderBy('name')->paginate();
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

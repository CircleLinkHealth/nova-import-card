<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/3/16
 * Time: 2:20 PM
 */

namespace App\Services\CPM;

use App\User;
use App\Models\CCD\Medication;
use App\CLH\Repositories\UserRepository;
use App\Repositories\CpmMedicationRepository;

class CpmMedicationService
{
    private $userRepo;
    private $medicationRepo;

    public function __construct(CpmMedicationRepository $medicationRepo, UserRepository $userRepo)
    {
        $this->medicationRepo = $medicationRepo;
        $this->userRepo = $userRepo;
    }

    public function repo()
    {
        return $this->medicationRepo;
    }

    public function medications()
    {
        return $this->repo()->model()->paginate();
    }

    public function search($terms)
    {
        return $this->repo()->search($terms);
    }

    public function editPatientMedication(Medication $medication)
    {
        if (!$medication) {
            throw new Exception('invalid parameters');
        } else {
            if (!$medication->id) {
                throw new Exception('parameter "id" is important');
            } elseif (!$medication->patient_id) {
                throw new Exception('parameter "patient_id" is important');
            } else {
                if (!$this->userRepo->exists($medication->patient_id)) {
                    throw new Exception('no user exists with id "' . $medication->patient_id . '"');
                } elseif (!$this->repo()->exists($medication->id)) {
                    throw new Exception('no medication exists with id "' . $medication->id . '"');
                } elseif ($this->repo()->model()->find($medication->id)->patient_id != $medication->patient_id) {
                    throw new Exception('user with id "' . $medication->patient_id . '" does own medication with id "' . $medication->id . '"');
                }
                return $this->repo()->editPatientMedication($medication);
            }
        }
    }
}

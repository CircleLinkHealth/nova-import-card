<?php namespace App\Services;

use App\User;
use App\Patient;
use App\Repositories\PatientWriteRepository;
use App\Services\CCD\CcdAllergyService;
use App\Repositories\UserRepositoryEloquent;

class PatientService
{
    private $patientRepo;
    private $userRepo;
    private $allergyRepo;

    public function __construct(PatientWriteRepository $patientRepo, CcdAllergyService $allergyService, UserRepositoryEloquent $userRepo) {
        $this->patientRepo = $patientRepo;
        $this->userRepo = $userRepo;
        $this->allergyService = $allergyService;
    }

    public function repo() {
        return $this->patientRepo;
    }

    public function getPatientByUserId($userId) {
        return $this->userRepo->model()->with(['patientInfo'])->find($userId)->patientInfo;
    }

    public function getCcdAllergies($userId) {
        return $this->allergyService->patientAllergies($userId);
    }

    public function setStatus($userId, $status) {
        $this->repo()->setStatus($userId, Patient::ENROLLED);
    }
}

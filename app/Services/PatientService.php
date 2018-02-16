<?php namespace App\Services;

use App\User;
use App\Patient;
use App\Repositories\PatientReadRepository;
use App\Repositories\PatientWriteRepository;
use App\Services\CCD\CcdAllergyService;
use App\Repositories\UserRepositoryEloquent;

class PatientService
{
    private $patientRepo;
    private $userRepo;
    private $allergyRepo;
    private $patientReadRepo;

    public function __construct(PatientWriteRepository $patientRepo, PatientReadRepository $patientReadRepo, CcdAllergyService $allergyService, UserRepositoryEloquent $userRepo) {
        $this->patientRepo = $patientRepo;
        $this->userRepo = $userRepo;
        $this->allergyService = $allergyService;
        $this->patientReadRepo = $patientReadRepo;
    }

    public function repo() {
        return $this->patientRepo;
    }
    
    public function readRepo() {
        return $this->patientReadRepo;
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

    public function patients() {
        return $this->readRepo()->patients();
    }
}

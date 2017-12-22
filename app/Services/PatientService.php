<?php namespace App\Services;

use App\User;
use App\Patient;
use App\Repositories\PatientRepository;
use App\Repositories\UserRepositoryEloquent;

class PatientService
{
    private $patientRepo;
    private $userRepo;

    public function __construct(PatientRepository $patientRepo, UserRepositoryEloquent $userRepo) {
        $this->patientRepo = $patientRepo;
        $this->userRepo = $userRepo;
    }

    function mapTypeFn ($type) {
        return function ($problem) use ($type) {
            $problem['type'] = $type;
            return $problem;
        };
    }

    public function getPatientByUserId($userId) {
        return $this->userRepo->with(['patientInfo'])->find($userId)->patientInfo;
    }

    public function getCpmProblems($userId) {
        $user = $this->userRepo->with(['patientInfo'])->find($userId);
        $patient = $user->patientInfo;
        return $user->cpmProblems()->with(['user'])->get()->map(function ($p) use ($user) {
            return [
                'id'   => $p->id,
                'name' => $p->name,
                'code' => $p->default_icd_10_code,
                'instructions' => $p->user->where('patient_id', $user->id)->first()->instruction()->get()
            ];
        })->toArray();
    }
    
    public function getCcdProblems($userId) {
        $user = $this->userRepo->with(['patientInfo'])->find($userId);
        $patient = $user->patientInfo;
        return $user->ccdProblems()->get()->map(function ($p) {
            return [
                'id'   => $p->id,
                'name' => $p->name,
                'code' => $p->icd_10_code
            ];
        })->toArray();
    }
}

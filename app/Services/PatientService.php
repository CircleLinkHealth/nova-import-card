<?php namespace App\Services;

use App\User;
use App\Patient;
use App\Repositories\PatientRepository;

class PatientService
{
    private $patientRepo;

    public function __construct(PatientRepository $patientRepo) {
        $this->patientRepo = $patientRepo;
    }

    function mapTypeFn ($type) {
        return function ($problem) use ($type) {
            $problem['type'] = $type;
            return $problem;
        };
    }

    public function getCpmProblems($id) {
        $patient = $this->$patientRepo->find($id)->with('user')->get();
        return $patient->user->cpmProblems()->get()->map(function ($p) use ($patientId) {
            return [
                'id'   => $p->id,
                'name' => $p->name,
                'code' => $p->default_icd_10_code,
                'instructions' => $p->user()->where('patient_id', $patientId)->first()->instruction()->get()
            ];
        })->toArray();
    }
    
    public function getCcdProblems($id) {
        $patient = $this->$patientRepo->find($id)->with('user')->get();
        return $patient->user->ccdProblems()->get()->map(function ($p) {
            return [
                'id'   => $p->id,
                'name' => $p->name,
                'code' => $p->icd_10_code
            ];
        })->toArray();
    }
}

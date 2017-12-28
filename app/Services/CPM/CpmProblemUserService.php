<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/3/16
 * Time: 2:19 PM
 */

namespace App\Services\CPM;

use App\Repositories\UserRepositoryEloquent;
use App\Repositories\CpmProblemUserRepository;

class CpmProblemUserService
{
    private $cpmProblemUserRepo;
    private $userRepo;

    public function __construct(CpmProblemUserRepository $cpmProblemUserRepo, UserRepositoryEloquent $userRepo) {
        $this->cpmProblemUserRepo = $cpmProblemUserRepo;
        $this->userRepo = $userRepo;
    }

    public function repo() {
        return $this->cpmProblemUserRepo;
    }

    public function addInstructionToProblem($patientId, $cpmProblemId, $instructionId) {
        $cpmProblemUser = $this->repo()->where([
            'patient_id' => $patientId,
            'cpm_problem_id' => $cpmProblemId,
            'cpm_instruction_id' => $instructionId
        ])->first();
        if (!$cpmProblemUser) {
            return $this->repo()->create($patientId, $cpmProblemId, $instructionId);
        }
        else {
            throw new Exception('a similar instruction->problem relationship already exists');
        }
    }
    
    public function removeInstructionFromProblem($patientId, $cpmProblemId, $instructionId) {
        $this->repo()->where([
            'patient_id' => $patientId,
            'cpm_problem_id' => $cpmProblemId,
            'cpm_instruction_id' => $instructionId
        ])->delete();
    }

    public function addProblemToPatient($patientId, $cpmProblemId) {
        return $this->repo()->create($patientId, $cpmProblemId, null);
    }
}

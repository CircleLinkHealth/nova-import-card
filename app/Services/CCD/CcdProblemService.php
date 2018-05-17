<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/3/16
 * Time: 2:19 PM
 */

namespace App\Services\CCD;

use App\Models\CPM\CpmProblem;
use App\User;
use App\Models\ProblemCode;
use App\Repositories\UserRepositoryEloquent;
use App\Repositories\CcdProblemRepository;
use App\Repositories\ProblemCodeRepository;
use App\Services\CPM\CpmInstructionService;
use App\Repositories\Criteria\CriteriaFactory;

class CcdProblemService
{
    private $problemRepo;
    private $userRepo;
    private $problemCodeRepo;
    private $instructionService;

    public function __construct(CcdProblemRepository $problemRepo, UserRepositoryEloquent $userRepo, 
                                ProblemCodeRepository $problemCodeRepo, CpmInstructionService $instructionService) {
        $this->problemRepo = $problemRepo;
        $this->userRepo = $userRepo;
        $this->problemCodeRepo = $problemCodeRepo;
        $this->instructionService = $instructionService;
    }

    public function repo() {
        return $this->problemRepo;
    }
        
    function setupProblem($p) {
        if ($p) {
            $problem = [
                'id'    => $p->id,
                'name'  => $p->name,
                'original_name' => $p->original_name,
                'cpm_id'  => $p->cpm_problem_id,
                'codes' => $p->codes()->get(),
                'is_monitored' => $p->is_monitored,
                'instruction' => $p->cpmInstruction()->first()
            ];
            return $problem;
        }
        return $p;
    }

    public function problems() {
        $problems = $this->repo()->problems();
        $problems->getCollection()->transform(function ($value) {
            return $this->setupProblem($value);
        });
        return $problems;
    }

    public function problem($id) {
        $problem = $this->repo()->model()->find($id);
        if ($problem) return $this->setupProblem($problem);
        else return null;
    }

    public function getPatientProblems($userId) {
        $user = $this->userRepo->model()->findOrFail($userId);

        //exclude generic diabetes type
        $diabetes = CpmProblem::where('name', 'Diabetes')->first();
        
        return $user->ccdProblems()->where('cpm_problem_id', '!=', $diabetes->id)->get()->map([$this, 'setupProblem']);
    }
    
    public function addPatientCcdProblem($ccdProblem) {
        if ($ccdProblem) {
            if ($ccdProblem['userId'] && $ccdProblem['name']) {

                $problem = $this->setupProblem($this->repo()->addPatientCcdProblem($ccdProblem));

                if ($problem && $ccdProblem['icd10']) {
                    $problemCode = new ProblemCode();
                    $problemCode->problem_id = $problem['id'];
                    $problemCode->problem_code_system_id = 2;
                    $problemCode->code = $ccdProblem['icd10'];
                    $this->problemCodeRepo->service()->add($problemCode);

                    return $this->problem($problem['id']);
                }
                else return $problem;

            }
            throw new Exception('$ccdProblem needs "userId" and "name" parameters');
        }
        throw new Exception('$ccdProblem should not be null');
    }

    public function editPatientCcdProblem($userId, $ccdId, $name, $problemCode = null, $is_monitored = null, $icd10 = null, $instruction = null) {
        $problem = $this->setupProblem($this->repo()->editPatientCcdProblem($userId, $ccdId, $name, $problemCode, $is_monitored));

        if ($instruction) {
            $instructionData = null;
            if ($problem['instruction']) {
                $instructionId = $problem['instruction']->id;
                $instructionData = $this->instructionService->edit($instructionId, $instruction);
            }
            else {
                $instructionData = $this->instructionService->create($instruction);
            }

            $problem['instruction'] = $instructionData;
            
            $this->repo()->model()->where([
                'id' => $ccdId
            ])->update([
                'cpm_instruction_id' => $instructionData->id
            ]);
        }
        else {
            $this->repo()->model()->where([
                'id' => $ccdId
            ])->update([
                'cpm_instruction_id' => null
            ]);
            $problem['instruction'] = null;
        }

        if ($icd10) {
            $problemCode = new ProblemCode();
            $problemCode->problem_id = $problem['id'];
            $problemCode->problem_code_system_id = 2;
            $problemCode->code = $icd10;
            $this->problemCodeRepo->service()->add($problemCode);
            
            return $this->problem($problem['id']);
        }
        return $problem;
    }
}

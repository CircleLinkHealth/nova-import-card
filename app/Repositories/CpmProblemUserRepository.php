<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 12/07/2017
 * Time: 12:32 PM
 */

namespace App\Repositories;


use App\Models\CPM\CpmProblemUser;

class CpmProblemUserRepository
{
    public function model()
    {
        return CpmProblemUser;
    }

    public function where($conditions) {
        return CpmProblemUser::where($conditions);
    }

    public function create($patientId, $cpmProblemId, $instructionId) {
        $cpmProblemUser= new CpmProblemUser();
        $cpmProblemUser->patient_id = $patientId;
        $cpmProblemUser->cpm_problem_id = $cpmProblemId;
        $cpmProblemUser->cpm_instruction_id = $instructionId;
        $cpmProblemUser->save();
        return $cpmProblemUser;
    }
}
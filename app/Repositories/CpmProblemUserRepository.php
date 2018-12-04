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

    public function where($conditions)
    {
        return CpmProblemUser::where($conditions);
    }

    public function create($patientId, $cpmProblemId, $instructionId)
    {
        $this->remove($patientId, $cpmProblemId);
        
        $cpmProblemUsers = CpmProblemUser::where([ 'cpm_problem_id' => $cpmProblemId, 'patient_id' => $patientId ])->orderBy('id', 'desc');
        $cpmProblemUser = $cpmProblemUsers->first();
        if ($cpmProblemUser && !$cpmProblemUser->cpm_instruction_id) {
            $cpmProblemUsers->update([
                'cpm_instruction_id' => $instructionId
            ]);
            $cpmProblemUser = $cpmProblemUsers->first();
        } else {
            $cpmProblemUser = new CpmProblemUser();
            $cpmProblemUser->patient_id = $patientId;
            $cpmProblemUser->cpm_problem_id = $cpmProblemId;
            $cpmProblemUser->cpm_instruction_id = $instructionId;
            $cpmProblemUser->save();
        }
        return $cpmProblemUser;
    }

    public function remove($patientId, $cpmProblemId)
    {
        $cpmProblemUsers = CpmProblemUser::where([ 'cpm_problem_id' => $cpmProblemId, 'patient_id' => $patientId ])->get();
        $cpmProblemUsers->map(function ($u) {
            $u->instruction()->delete();
            $u->delete();
        });
    }
}

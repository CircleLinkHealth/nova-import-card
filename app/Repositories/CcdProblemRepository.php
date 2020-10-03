<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use CircleLinkHealth\CcmBilling\Events\PatientProblemsChanged;
use CircleLinkHealth\Patientapi\ValueObjects\CcdProblemInput;
use CircleLinkHealth\SharedModels\Entities\Problem as CcdProblem;
use Illuminate\Support\Facades\DB;

class CcdProblemRepository
{
    /**
     * @param {[
     *    userId,
     *    name,
     *    is_monitored,
     *    cpm_problem_id,
     *    icd10
     * ]} $ccdProblem
     */
    public function addPatientCcdProblem(CcdProblemInput $ccdProblem)
    {
        return CcdProblem::firstOrCreate([
            'patient_id' => $ccdProblem->getUserId(),
            'name'       => $ccdProblem->getName(),
        ], [
            'cpm_problem_id' => $ccdProblem->getCpmProblemId(),
            'is_monitored'   => $ccdProblem->getIsMonitored(),
        ]);
    }

    public function count()
    {
        return CcdProblem::select('name', DB::raw('count(*) as total'))->groupBy('name')->pluck('total')->count();
    }

    public function editPatientCcdProblem($userId, $ccdProblemId, $problemCode = null, $is_monitored = null)
    {
        $problem = CcdProblem::where(['id' => $ccdProblemId, 'patient_id' => $userId])->first();

        if ($problem) {
            $problem->cpm_problem_id = $problemCode;
            $problem->is_monitored   = $is_monitored;
            $problem->save();

            event(new PatientProblemsChanged($userId));
        }

        return $problem;
    }

    public function patientCcdExists($userId, $name)
    {
        return (bool) CcdProblem::where(['patient_id' => $userId, 'name' => $name])->first();
    }

    public function patientIds($name)
    {
        return CcdProblem::where(['name' => $name])->distinct(['patient_id'])->get(['patient_id']);
    }

    public function problem($id)
    {
        return CcdProblem::findOrFail($id);
    }

    public function removePatientCcdProblem($userId, $ccdId)
    {
        CcdProblem::where(['patient_id' => $userId, 'id' => $ccdId])->delete();

        return [
            'message' => 'successful',
        ];
    }
}

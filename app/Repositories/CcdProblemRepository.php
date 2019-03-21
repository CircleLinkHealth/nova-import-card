<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use App\Models\CCD\Problem;
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
    public function addPatientCcdProblem($ccdProblem)
    {
        if ( ! $this->patientCcdExists($ccdProblem['userId'], $ccdProblem['name'])) {
            $problem                 = new Problem();
            $problem->patient_id     = $ccdProblem['userId'];
            $problem->name           = $ccdProblem['name'];
            $problem->cpm_problem_id = $ccdProblem['cpm_problem_id'];
            $problem->is_monitored   = $ccdProblem['is_monitored'];
            $problem->save();

            return $problem;
        }

        return $this->model()->where(['patient_id' => $ccdProblem['userId'], 'name' => $ccdProblem['name']])->first();
    }

    public function count()
    {
        return $this->model()->select('name', DB::raw('count(*) as total'))->groupBy('name')->pluck('total')->count();
    }

    public function editPatientCcdProblem($userId, $ccdId, $name, $problemCode = null, $is_monitored = null)
    {
        if ($this->patientCcdExists($userId, $name)) {
            $this->model()->where(['id' => $ccdId, 'patient_id' => $userId])->update([
                'name'           => $name,
                'cpm_problem_id' => $problemCode,
                'is_monitored'   => $is_monitored,
            ]);
        }

        return $this->model()->where(['id' => $ccdId, 'patient_id' => $userId])->first();
    }

    public function model()
    {
        return app(Problem::class);
    }

    public function patientCcdExists($userId, $name)
    {
        return (bool) $this->model()->where(['patient_id' => $userId, 'name' => $name])->first();
    }

    public function patientIds($name)
    {
        return $this->model()->where(['name' => $name])->distinct(['patient_id'])->get(['patient_id']);
    }

    public function problem($id)
    {
        return $this->model()->findOrFail($id);
    }

    public function problems()
    {
        return $this->model()->groupBy('name')->orderBy('id')->paginate(30);
    }

    public function removePatientCcdProblem($userId, $ccdId)
    {
        $this->model()->where(['patient_id' => $userId, 'id' => $ccdId])->first()->delete();

        return [
            'message' => 'successful',
        ];
    }
}

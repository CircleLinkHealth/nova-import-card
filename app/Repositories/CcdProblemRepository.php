<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 12/07/2017
 * Time: 12:32 PM
 */

namespace App\Repositories;

use App\User;
use App\Patient;
use App\Models\CCD\Problem;
use Illuminate\Support\Facades\DB;

class CcdProblemRepository
{
    public function model()
    {
        return app(Problem::class);
    }

    public function count() {
        return $this->model->select('name', DB::raw('count(*) as total'))->groupBy('name')->pluck('total')->count();
    }

    public function patientIds($name) {
        return $this->model()->where(['name' => $name ])->distinct(['patient_id'])->get(['patient_id']);
    }

    public function problems() {
        return $this->model()->groupBy('name')->orderBy('id')->paginate(30);
    }

    public function problem($id) {
        return $this->model()->findOrFail($id);
    }

    public function patientCcdExists($userId, $name) {
        return !!$this->model()->where([ 'patient_id' => $userId, 'name' => $name ])->first();
    }

    public function addPatientCcdProblem($userId, $name, $problemCode = null) {
        if (!$this->patientCcdExists($userId, $name)) {
            $problem = new Problem();
            $problem->patient_id = $userId;
            $problem->name = $name;
            $problem->cpm_problem_id = $problemCode;
            $problem->save();
            return $problem;
        }
        return $this->model()->where([ 'patient_id' => $userId, 'name' => $name ])->first();
    }

    public function removePatientCcdProblem($userId, $ccdId) {
        $this->model()->where([ 'patient_id' => $userId, 'id' => $ccdId ])->delete();
        return [
            'message' => 'successful'
        ];
    }
}
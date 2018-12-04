<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use App\Models\CPM\CpmSymptom;
use App\Models\CPM\CpmSymptomUser;

class CpmSymptomRepository
{
    public function addSymptomToPatient($symptomId, $userId)
    {
        if (!$this->patientHasSymptom($userId, $symptomId)) {
            $symptomUser                 = new CpmSymptomUser();
            $symptomUser->cpm_symptom_id = $symptomId;
            $symptomUser->patient_id     = $userId;
            $symptomUser->save();

            return $symptomUser;
        }
    }

    public function count()
    {
        return $this->model()->count();
    }

    public function model()
    {
        return app(CpmSymptom::class);
    }

    public function patientHasSymptom($userId, $symptomId)
    {
        return (bool) CpmSymptomUser::where([
            'patient_id'     => $userId,
            'cpm_symptom_id' => $symptomId,
        ])->first();
    }

    public function patientSymptoms($userId)
    {
        return CpmSymptomUser::where(['patient_id' => $userId])->with('cpmSymptom')->get()->map(function ($u) {
            return $u->cpmSymptom;
        });
    }

    public function removeSymptomFromPatient($symptomId, $userId)
    {
        if ($this->patientHasSymptom($userId, $symptomId)) {
            CpmSymptomUser::where([
                'patient_id'     => $userId,
                'cpm_symptom_id' => $symptomId,
            ])->delete();

            return [
                'message' => 'successful',
            ];
        }

        return null;
    }

    public function symptoms()
    {
        return $this->model()->paginate();
    }
}

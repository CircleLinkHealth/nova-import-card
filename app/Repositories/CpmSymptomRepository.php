<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use CircleLinkHealth\SharedModels\Entities\CpmSymptom;
use CircleLinkHealth\SharedModels\Entities\CpmSymptomUser;

class CpmSymptomRepository
{
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

    public function symptoms()
    {
        return $this->model()->paginate();
    }
}

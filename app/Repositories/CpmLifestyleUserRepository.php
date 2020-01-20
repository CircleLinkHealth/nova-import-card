<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use CircleLinkHealth\SharedModels\Entities\CpmLifestyleUser;

class CpmLifestyleUserRepository
{
    public function addLifestyleToPatient($lifestyleId, $userId)
    {
        if ( ! $this->patientHasLifestyle($userId, $lifestyleId)) {
            $lifestyleUser                   = new CpmLifestyleUser();
            $lifestyleUser->patient_id       = $userId;
            $lifestyleUser->cpm_lifestyle_id = $lifestyleId;
            $lifestyleUser->save();

            return $lifestyleUser;
        }

        return $this->model()->where(['patient_id' => $userId, 'cpm_lifestyle_id' => $lifestyleId])->first();
    }

    public function count()
    {
        return $this->model()->count();
    }

    public function lifestylePatients($lifestyleId)
    {
        return $this->model()->where(['cpm_lifestyle_id' => $lifestyleId])->get(['patient_id'])->map(function ($l) {
            return $l->patient_id;
        });
    }

    public function model()
    {
        return app(CpmLifestyleUser::class);
    }

    public function patientHasLifestyle($userId, $lifestyleId)
    {
        return (bool) $this->model()->where(['patient_id' => $userId, 'cpm_lifestyle_id' => $lifestyleId])->first();
    }

    public function removeLifestyleFromPatient($lifestyleId, $userId)
    {
        $this->model()->where(['patient_id' => $userId, 'cpm_lifestyle_id' => $lifestyleId])->delete();

        return [
            'message' => 'successful',
        ];
    }
}

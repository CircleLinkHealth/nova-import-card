<?php

namespace App\Repositories;

use App\User;
use App\Models\CPM\CpmLifestyleUser;

class CpmLifestyleUserRepository
{
    public function model()
    {
        return app(CpmLifestyleUser::class);
    }

    public function count() {
        return $this->model()->count();
    }
    
    public function lifestylePatients($lifestyleId) {
        return $this->model()->where([ 'cpm_lifestyle_id' => $lifestyleId ])->get(['patient_id'])->map(function ($l) {
            return $l->patient_id;
        });
    }

    public function patientLifestyles($userId) {
        return $this->model()->where([ 'patient_id' => $userId ])->with(['cpmLifestyle', 'cpmInstruction'])->get()->map(function ($u) {
            $lifestyle = $u->cpmLifestyle;
            $lifestyle['instruction'] = $u->cpmInstruction;
            return $u->cpmLifestyle;
        });
    }
}
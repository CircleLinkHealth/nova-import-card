<?php

namespace App\Repositories;

use App\User;
use App\Patient;
use App\Models\CPM\CpmSymptom;
use App\Models\CPM\CpmSymptomUser;

class CpmSymptomRepository
{
    public function model()
    {
        return app(CpmSymptom::class);
    }

    public function count() {
        return $this->model()->count();
    }

    public function symptoms() {
        return $this->model()->paginate();
    }

    public function patientSymptoms($userId) {
        return CpmSymptomUser::where([ 'patient_id' => $userId ])->with('cpmSymptom')->get()->map(function ($u) {
            return $u->cpmSymptom;
        });
    }
}
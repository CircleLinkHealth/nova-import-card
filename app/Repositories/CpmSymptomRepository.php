<?php

namespace App\Repositories;

use App\User;
use App\Patient;
use App\Models\CPM\CpmSymptom;

class CpmSymptomRepository
{
    public function model()
    {
        return app(CpmSymptom::class);
    }

    public function count() {
        return $this->model()->count();
    }
}
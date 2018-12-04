<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use App\Models\CPM\CpmBiometric;

class CpmBiometricRepository
{
    public function biometrics()
    {
        return $this->model()->get();
    }

    public function model()
    {
        return app(CpmBiometric::class);
    }
}

<?php

namespace App\Repositories;

use App\CarePlan;

class CareplanRepository
{
    public function model()
    {
        return app(CarePlan::class);
    }
}
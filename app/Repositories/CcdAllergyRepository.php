<?php

namespace App\Repositories;

use App\Models\CCD\Allergy;

class CareplanRepository
{

    public function model()
    {
        return app(Allergy::class);
    }
    
}
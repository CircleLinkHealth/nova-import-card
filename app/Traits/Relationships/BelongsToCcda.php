<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits\Relationships;

use App\Models\MedicalRecords\Ccda;

trait BelongsToCcda
{
    public function ccda()
    {
        return $this->belongsTo(Ccda::class);
    }
}

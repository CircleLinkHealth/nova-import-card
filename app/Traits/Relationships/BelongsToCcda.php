<?php

namespace App\Traits\Relationships;


use App\Models\MedicalRecords\Ccda;

trait BelongsToCcda
{
    public function ccda()
    {
        return $this->belongsTo(Ccda::class);
    }
}
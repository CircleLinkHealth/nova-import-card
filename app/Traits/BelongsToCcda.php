<?php

namespace App\Traits;


use App\Models\MedicalRecords\Ccda;

trait BelongsToCcda
{
    public function ccda()
    {
        return $this->belongsTo(Ccda::class);
    }
}
<?php

namespace App\Traits;


use App\Models\CCD\Ccda;

trait BelongsToCcda
{
    public function ccda()
    {
        return $this->belongsTo(Ccda::class);
    }
}
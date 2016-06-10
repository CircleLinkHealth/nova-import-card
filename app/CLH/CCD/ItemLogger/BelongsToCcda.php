<?php

namespace App\CLH\CCD\ItemLogger;


use App\Models\CCD\Ccda;

trait BelongsToCcda
{
    public function ccda()
    {
        return $this->belongsTo(Ccda::class);
    }
}
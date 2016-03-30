<?php

namespace App\CLH\CCD\ItemLogger;


use App\CLH\CCD\Ccda;

trait BelongsToCcda
{
    public function ccda()
    {
        return $this->belongsTo(Ccda::class);
    }
}
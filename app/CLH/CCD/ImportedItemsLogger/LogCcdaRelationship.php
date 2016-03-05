<?php

namespace App\CLH\CCD\ImportedItemsLogger;


use App\CLH\CCD\Ccda;

trait LogCcdaRelationship
{
    public function ccda()
    {
        return $this->belongsTo(Ccda::class);
    }
}
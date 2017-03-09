<?php

namespace App\Traits\Relationships;


use App\Models\CCD\CcdVendor;

trait BelongsToVendor
{
    public function vendor()
    {
        return $this->belongsTo(CcdVendor::class);
    }
}
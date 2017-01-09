<?php

namespace App\Traits;


use App\Models\CCD\CcdVendor;

trait BelongsToVendor
{
    public function vendor()
    {
        return $this->belongsTo(CcdVendor::class);
    }
}
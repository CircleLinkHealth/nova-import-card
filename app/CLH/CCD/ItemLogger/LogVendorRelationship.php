<?php

namespace App\CLH\CCD\ItemLogger;


use App\Models\CCD\CcdVendor;

trait LogVendorRelationship
{
    public function vendor()
    {
        return $this->belongsTo(CcdVendor::class);
    }
}
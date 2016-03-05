<?php

namespace App\CLH\CCD\ImportedItemsLogger;


use App\CLH\CCD\Vendor\CcdVendor;

trait LogVendorRelationship
{
    public function vendor()
    {
        return $this->belongsTo(CcdVendor::class);
    }
}
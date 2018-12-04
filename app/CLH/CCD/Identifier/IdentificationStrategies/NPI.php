<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CLH\CCD\Identifier\IdentificationStrategies;

use App\Models\CCD\CcdVendor;

class NPI extends BaseIdentificationStrategy
{
    public function identify()
    {
        if (empty($this->ccd->document->documentation_of)) {
            return false;
        }

        $providers = (array) $this->ccd->document->documentation_of;

        $vendorNpis = CcdVendor::pluck('doctor_oid')->all();

        return array_filter($vendorNpis, function ($vendorNpi) use ($providers) {
            return in_array($vendorNpi, array_column($providers, 'npi'));
        });
    }
}

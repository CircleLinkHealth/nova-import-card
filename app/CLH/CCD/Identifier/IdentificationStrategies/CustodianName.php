<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CLH\CCD\Identifier\IdentificationStrategies;

class CustodianName extends BaseIdentificationStrategy
{
    public function identify()
    {
        if (empty($this->ccd->document->custodian->name)) {
            return false;
        }

        $custodianName = $this->ccd->document->custodian->name;

        return empty($custodianName) ? false : trim($custodianName);
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CLH\CCD\Identifier\IdentificationStrategies;

class RepresentedOrganizationId extends BaseIdentificationStrategy
{
    public function identify()
    {
        if (empty($this->ccd->document->legal_authenticator->representedOrganization->ids[0]->root)) {
            return false;
        }

        $ehrOid = $this->parseEhrOid($this->ccd->document->legal_authenticator->representedOrganization->ids[0]->root);

        return empty($ehrOid) ? false : $ehrOid;
    }

    public function parseEhrOid($oid)
    {
        $oidParts = explode('.', $oid);

        return empty($oidParts[6]) ? false : $oidParts[6];
    }
}

<?php

namespace App\CLH\CCD\Identifier\IdentificationStrategies;

class RepresentedOrganizationDoctorId extends BaseIdentificationStrategy
{
    public function identify()
    {
        if (empty($this->ccd->document->legal_authenticator->representedOrganization->ids[0]->root)) {
            return false;
        }

        $doctorOid = $this->parseDoctorOid($this->ccd->document->legal_authenticator->representedOrganization->ids[0]->root);

        return empty($doctorOid) ? false : $doctorOid;
    }

    public function parseDoctorOid($oid)
    {
        $oidParts = explode('.', $oid);

        return empty($oidParts[7]) ? false : $oidParts[7];
    }
}

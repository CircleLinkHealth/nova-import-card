<?php

namespace App\CLH\CCD\Identifier\IdentificationStrategies;


class NPI extends BaseIdentificationStrategy
{

    public function identify()
    {
        if (empty($this->ccd->document->documentation_of->npi)) return false;

        return $this->ccd->document->documentation_of->npi;
    }
}
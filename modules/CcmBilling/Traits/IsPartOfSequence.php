<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Traits;

use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessor;

trait IsPartOfSequence
{
    //todo:deprecate
    abstract public function next(): ?PatientServiceProcessor;

    abstract public function previous(): ?PatientServiceProcessor;
}

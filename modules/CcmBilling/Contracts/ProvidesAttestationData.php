<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

use Carbon\Carbon;

interface ProvidesAttestationData
{
    public function getAddendumId(): ?int;

    public function getAttestorId(): ?int;

    public function getCallId(): ?int;

    public function getChargeableMonth(): ?Carbon;

    //todo:deprecate
    public function getPmsId(): ?int;

    public function getProblemIds(): array;
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Contracts\MedicalRecord\Section;

interface Logger
{
    public function handle($problemsString): array;

    public function shouldHandle($problems);
}

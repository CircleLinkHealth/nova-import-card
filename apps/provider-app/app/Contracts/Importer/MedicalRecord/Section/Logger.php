<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts\Importer\MedicalRecord\Section;

interface Logger
{
    public function handle($problemsString): array;

    public function shouldHandle($problems);
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts\Repositories;

interface AprimaCcdApiRepository
{
    public function getPatientAndProviderIdsByLocationAndForeignSystem($locationId, $foreignSystem);
}

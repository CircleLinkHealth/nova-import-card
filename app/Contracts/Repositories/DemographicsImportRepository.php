<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface DemographicsImportRepository.
 */
interface DemographicsImportRepository extends RepositoryInterface
{
    public function getPatientAndProviderIdsByLocationAndForeignSystem($locationId, $foreignSystem);
}

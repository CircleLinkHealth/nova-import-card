<?php

namespace App\Contracts\Repositories;

use App\Entities\DemographicsImport;
use App\Repositories\Activity;
use App\Repositories\Ccda;
use App\Repositories\DB;
use App\Repositories\ForeignId;
use App\Repositories\User;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface DemographicsImportRepository
 * @package namespace App\Contracts\Repositories;
 */
interface DemographicsImportRepository extends RepositoryInterface
{
    public function getPatientAndProviderIdsByLocationAndForeignSystem($locationId, $foreignSystem);
}

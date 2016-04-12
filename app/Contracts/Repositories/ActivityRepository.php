<?php

namespace App\Contracts\Repositories;

use App\Activity;
use App\Repositories\CcmTimeApiLog;
use App\Repositories\DB;
use App\Repositories\User;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface ActivityRepository
 * @package namespace App\Contracts\Repositories;
 */
interface ActivityRepository extends RepositoryInterface
{
    public function getCcmActivities($patientId, $providerId, $startDate, $endDate, $sendAll = false);
}

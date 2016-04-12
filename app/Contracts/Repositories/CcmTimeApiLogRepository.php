<?php

namespace App\Contracts\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface CcmTimeApiLogRepository
 * @package namespace App\Contracts\Repositories;
 */
interface CcmTimeApiLogRepository extends RepositoryInterface
{
    public function logSentActivity(array $attributes, array $values = array());
}

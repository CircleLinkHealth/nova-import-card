<?php

namespace App\Contracts\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface CcmTimeApiLogRepository
 * @package namespace App\Contracts\Repositories;
 */
interface CcmTimeApiLogRepository extends RepositoryInterface
{
    /**
     * Log Activities already sent to Aprima so that we'll know not to send them again.
     * @param array $attributes
     * @param array $values
     * @return mixed
     */
    public function logSentActivity(array $attributes, array $values = []);
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface CcmTimeApiLogRepository.
 */
interface CcmTimeApiLogRepository extends RepositoryInterface
{
    /**
     * Log Activities already sent to Aprima so that we'll know not to send them again.
     *
     * @param array $attributes
     * @param array $values
     *
     * @return mixed
     */
    public function logSentActivity(array $attributes, array $values = []);
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface ActivityRepository.
 */
interface ActivityRepository extends RepositoryInterface
{
    /**
     * Get all CCM Activities
     * Query by ProviderId, PatientId and Dates
     * Set sendAll to true to return all activities regardless of dates.
     *
     * @param $patientId
     * @param $providerId
     * @param $startDate
     * @param $endDate
     * @param bool $sendAll
     *
     * @return mixed
     */
    public function getCcmActivities($patientId, $providerId, $startDate, $endDate, $sendAll = false);
}

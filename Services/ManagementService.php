<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Services;

use CircleLinkHealth\CpmAdmin\Repositories\CallRepository;
use Carbon\Carbon;

class ManagementService
{
    private $repository;

    public function __construct(CallRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getPatientsWithoutAnyInboundCalls($practiceId)
    {
        return $this->repository->patientsWithoutAnyInboundCalls($practiceId);
    }

    public function getPatientsWithoutScheduledActivities($practiceId, Carbon $afterDate = null)
    {
        return $this->repository->patientsWithoutScheduledActivities($practiceId, $afterDate);
    }

    public function getPatientsWithoutScheduledCalls($practiceId, Carbon $afterDate = null)
    {
        return $this->repository->patientsWithoutScheduledCalls($practiceId, $afterDate);
    }

    public function getScheduledCalls(Carbon $month = null)
    {
        return $this->repository->scheduledCalls($month);
    }
}

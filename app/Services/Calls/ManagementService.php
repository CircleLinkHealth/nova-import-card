<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 12/11/2017
 * Time: 3:00 PM
 */

namespace App\Services\Calls;

use App\Repositories\CallRepository;
use Carbon\Carbon;

class ManagementService
{
    private $repository;

    public function __construct(CallRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getScheduledCalls(Carbon $month = null)
    {
        return $this->repository->scheduledCalls($month);
    }

    public function getPatientsWithoutScheduledCalls($practiceId, Carbon $afterDate = null)
    {
        return $this->repository->patientsWithoutScheduledCalls($practiceId, $afterDate);
    }

    public function getPatientsWithoutAnyInboundCalls($practiceId)
    {
        return $this->repository->patientsWithoutAnyInboundCalls($practiceId);
    }
}

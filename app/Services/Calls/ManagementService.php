<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 12/11/2017
 * Time: 3:00 PM
 */

namespace App\Services\Calls;


use App\Repositories\CallRepository;

class ManagementService
{
    private $repository;

    public function __construct(CallRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getScheduledCalls() {
        return $this->repository->scheduledCalls();
    }
}
<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Repositories\CallRepository;

class CallService
{
    private $callRepo;

    public function __construct(CallRepository $callRepo)
    {
        $this->callRepo = $callRepo;
    }

    public function repo()
    {
        return $this->callRepo;
    }
}

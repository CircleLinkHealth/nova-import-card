<?php

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

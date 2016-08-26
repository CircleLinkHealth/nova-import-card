<?php

namespace App\Http\Controllers\CcdApi\Athena;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Services\AthenaAPI\Calls;
use App\Services\AthenaAPI\Service;
use Carbon\Carbon;

class AthenaApiController extends Controller
{
    private $service;

    public function __construct(Service $athenaApi)
    {
        $this->service = $athenaApi;
    }

    public function getTodays()
    {
        $this->service->getAppointmentsForToday(1959188);
        $this->service->getCcdsFromRequestQueue(5);
    }

}



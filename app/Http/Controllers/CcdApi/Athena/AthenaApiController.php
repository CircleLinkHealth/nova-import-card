<?php

namespace App\Http\Controllers\CcdApi\Athena;

use App\ForeignId;
use App\Http\Controllers\Controller;
use App\Models\CCD\CcdVendor;
use App\Services\AthenaAPI\Service;

class AthenaApiController extends Controller
{
    private $service;

    public function __construct(Service $athenaApi)
    {
        $this->service = $athenaApi;
    }

    public function getTodays()
    {
        $vendors = CcdVendor::whereEhrName(ForeignId::ATHENA)->get();

        foreach ($vendors as $vendor)
        {
            $this->service->getAppointmentsForToday($vendor->practice_id);
            $this->service->getCcdsFromRequestQueue(5);
        }
    }

}



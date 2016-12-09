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

        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subMonth(1);

        foreach ($vendors as $vendor)
        {
            $this->service->getAppointments($vendor->practice_id, $startDate, $endDate);
            $this->service->getCcdsFromRequestQueue(5);
        }
    }

}



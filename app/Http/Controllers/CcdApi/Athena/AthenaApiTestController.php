<?php

namespace App\Http\Controllers\CcdApi\Athena;

use App\ForeignId;
use App\Http\Controllers\Controller;
use App\Models\CCD\CcdVendor;
use App\Services\AthenaAPI\Calls;
use App\Services\AthenaAPI\Service;
use Carbon\Carbon;

class AthenaApiTestController extends Controller
{
    private $service;

    public function __construct(Service $athenaApi)
    {
        $this->service = $athenaApi;
    }

    public function getTodays()
    {
        $vendors = CcdVendor::whereEhrName(ForeignId::ATHENA)->get();

        $endDate = Carbon::today()->addDay(1);
        $startDate = $endDate->copy()->subDay(2);

        foreach ($vendors as $vendor) {
            $this->service->getAppointments(1959188, $startDate, $endDate);
            $this->service->getCcdsFromRequestQueue(5);
        }
    }

    public function getCcdas(
        array $patientIds,
        $practiceId,
        $departmentId
    ) {
        $api = new Calls();

        foreach ($patientIds as $id) {
            $ccda = $api->getCcd($id, $practiceId, $departmentId);
            file_put_contents(storage_path('AthenaCcdas/' . $id . '.xml'), $ccda[0]['ccda']);
        }

    }
}



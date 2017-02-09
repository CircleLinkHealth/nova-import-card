<?php

namespace App\Http\Controllers\CcdApi\Athena;

use App\ForeignId;
use App\Http\Controllers\Controller;
use App\Models\CCD\CcdVendor;
use App\Services\AthenaAPI\Calls;
use App\Services\AthenaAPI\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

        $endDate = Carbon::today()->addDay(1);
        $startDate = $endDate->copy()->subDay(2);

        foreach ($vendors as $vendor) {
            $this->service->getAppointments(1959188, $startDate, $endDate);
            $this->service->getCcdsFromRequestQueue(5);
        }
    }


    public function fetchCcdas(
        Request $request,
        $practiceId,
        $departmentId
    ) {
        if ($ids = $request->input('ids') == null) {
            return 'Please include IDs';
        }

        $ids = explode(',', $ids);
    }

    private function getCcdas(
        array $patientIds,
        $practiceId,
        $departmentId
    ) {
        $api = new Calls();

        foreach ($patientIds as $id) {
            $ccda = $api->getCcd($id, $practiceId, $departmentId);
            file_put_contents(storage_path('ccdas/' . $id . '.xml'), $ccda[0]['ccda']);
        }

    }
}



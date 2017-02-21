<?php

namespace App\Http\Controllers\CcdApi\Athena;

use App\ForeignId;
use App\Http\Controllers\Controller;
use App\Models\CCD\CcdVendor;
use App\Models\MedicalRecords\Ccda;
use App\Practice;
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

    public function getCcdas(Request $request)
    {
        $api = new Calls();

        $imported = [];
        $practice = Practice::find($request->input('practice_id'));

        $practiceId = $practice->external_id;
        $departmentId = $practice->locations->first()->external_department_id;
        $patientIds = array_filter(explode(',', $request->input('ids')), 'trim');

        foreach ($patientIds as $id) {
            $ccdaExternal = $api->getCcd($id, $practiceId, $departmentId);

            $ccda = Ccda::create([
                'practice_id' => $practice->id,
                'location_id' => $practice->locations->first()->id,
                'user_id'     => auth()->user()->id,
                'vendor_id'   => 1,
                'xml'         => $ccdaExternal[0]['ccda'],
            ]);

            $imported[] = $ccda->import();
        }

        return count($imported) . " CCDs were imported. To finish the importing process go to:  " . link_to_route('view.files.ready.to.import');


    }
}



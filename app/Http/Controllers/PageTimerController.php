<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\ValueObjects\PatientChargeableServiceForTimeTracker;
use CircleLinkHealth\CcmBilling\Domain\Patient\PatientServicesForTimeTracker;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\SharedModels\Entities\PageTimer;
use CircleLinkHealth\TimeTracking\Jobs\StoreTimeTracking;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

class PageTimerController extends Controller
{
    public function getTimeForPatients(Request $request)
    {
        $patients = $request->get('patients', []);

        if (empty($patients)) {
            return response()->json([]);
        }

        $result = collect();
        // todo: improve this. should get times for all patients at once
        foreach ($patients as $patientId) {
            $id                 = (int) $patientId;
            $chargeableServices = $this->getChargeableServices($id);
            $csArray            = [];
            foreach ($chargeableServices as $entry) {
                if ( ! $entry->chargeable_service_id) {
                    continue;
                }
                $csArray[] = (new PatientChargeableServiceForTimeTracker($entry->chargeable_service_id, $entry->total_time))->toArray();
            }
            $result->put($id, $csArray);
        }

        return response()->json($result);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $pageTimes = PageTimer::orderBy('id', 'desc')->paginate(10);

        return view('pageTimer.index', ['pageTimes' => $pageTimes]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $pageTime = PageTimer::find($id);

        return view('pageTimer.show', ['pageTime' => $pageTime]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $params = new ParameterBag($request->input());
        $params->add(['userAgent' => $request->userAgent()]);

        StoreTimeTracking::dispatch($params)
            ->onQueue(getCpmQueueName(CpmConstants::HIGH_QUEUE));

        return response('PageTimer activities logged.', 201);
    }

    private function getChargeableServices($patientId)
    {
        return (new PatientServicesForTimeTracker((int) $patientId, now()))->get();
    }
}

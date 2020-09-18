<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use CircleLinkHealth\TimeTracking\Jobs\StoreTimeTracking;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\SharedModels\Entities\PageTimer;
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

        $times = PatientMonthlySummary::whereIn('patient_id', $patients)
            ->whereMonthYear(Carbon::now()->startOfMonth())
            ->orderBy('id', 'desc')
            ->get([
                'ccm_time',
                'patient_id',
            ])
            ->mapWithKeys(function ($p) {
                return [
                    $p->patient_id => [
                        'ccm_time' => $p->ccm_time ?? 0,
                        'bhi_time' => $p->bhi_time ?? 0,
                    ],
                ];
            })
            ->all();

        return response()->json($times);
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
            ->onQueue('high');

        return response('PageTimer activities logged.', 201);
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use CircleLinkHealth\TimeTracking\Services\ActivityService;

class ActivityController extends Controller
{
    private $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * Get the CCM Time provided by a specific provider to a specific patient during the current month.
     *
     * @param $providerId
     * @param $patientId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function between($providerId, $patientId)
    {
        return response()->json($this->activityService->ccmTimeBetween($providerId, [$patientId]));
    }

    /**
     * Get total CCM Time for a patient for the current month.
     *
     * @param $patientId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ccmTime($patientId)
    {
        return response()->json($this->activityService->totalCcmTime($patientId));
    }
}

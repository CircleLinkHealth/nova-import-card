<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ActivityService;

class CcmTimeController extends Controller
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
}

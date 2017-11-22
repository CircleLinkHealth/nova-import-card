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

    public function between($providerId, $patientId)
    {
        return response()->json($this->activityService->ccmTimeBetween($providerId, [$patientId]));
    }
}

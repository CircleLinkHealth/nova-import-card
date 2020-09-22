<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers\API;

use App\Filters\NurseFilters;
use App\Http\Controllers\API\ApiController;
use App\Http\Resources\NurseInfo;
use CircleLinkHealth\Customer\Entities\Nurse;
use Illuminate\Http\Request;

class NurseController extends ApiController
{
    /**
     * @SWG\GET(
     *     path="/nurses",
     *     tags={"nurses"},
     *     summary="Get Nurses Info",
     *     description="Display a listing of nurses",
     *     @SWG\Response(
     *         response="default",
     *         description="A listing of nurses"
     *     )
     *   )
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request, NurseFilters $filters)
    {
        $filtered = Nurse::filter($filters)->get();

        return NurseInfo::collection($filtered);
    }
}

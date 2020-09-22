<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers\API;

use CircleLinkHealth\CpmAdmin\Filters\NurseFilters;
use CircleLinkHealth\Customer\Http\Resources\NurseInfo;
use CircleLinkHealth\Customer\Entities\Nurse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class NurseController extends Controller
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

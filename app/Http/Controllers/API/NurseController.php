<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

use App\Filters\NurseFilters;
use App\Http\Resources\NurseInfo;
use CircleLinkHealth\Customer\Entities\Nurse;

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
    public function index(NurseFilters $filters)
    {
        return NurseInfo::collection(Nurse::filter($filters)->get());
    }
}

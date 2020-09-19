<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers\API;

use CircleLinkHealth\SharedModels\Entities\CallView;
use CircleLinkHealth\SharedModels\Filters\CallViewFilters;
use CircleLinkHealth\CpmAdmin\Http\Resources\CallView as CallViewResource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CallsViewController extends Controller
{
    public function __construct()
    {
    }

    /**
     * @SWG\GET(
     *     path="/admin/calls",
     *     tags={"calls"},
     *     summary="Get Calls Info",
     *     description="Display a listing of calls",
     *     @SWG\Header(header="X-Requested-With", type="String", default="XMLHttpRequest"),
     *     @SWG\Response(
     *         response="default",
     *         description="A listing of calls"
     *     )
     *   )
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, CallViewFilters $filters)
    {
        $rows  = $request->input('rows');
        $calls = CallView::filter($filters)
            ->paginate($rows ?? 15);

        return CallViewResource::collection($calls);
    }
}

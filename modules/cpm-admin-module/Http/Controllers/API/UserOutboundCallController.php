<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers\API;

use CircleLinkHealth\CpmAdmin\Http\Requests\AssignCallsToUser;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Routing\Controller;

class UserOutboundCallController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param $user
     *
     * @return \Illuminate\Http\Response
     */
    public function store(AssignCallsToUser $request, $user)
    {
        $callIds = $request->input('callIds');

        $nurseUser = User::findOrFail($user);
        $calls     = $nurseUser->assignOutboundCalls($callIds);

        return response()->json();
    }
}

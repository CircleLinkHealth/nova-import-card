<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignCallsToUser;
use CircleLinkHealth\Customer\Entities\User;

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

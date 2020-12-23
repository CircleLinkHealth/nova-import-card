<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Http\Requests\LoginFromHerokuRequest;

class LoginFromHerokuController extends Controller
{
    public function loginUser(LoginFromHerokuRequest $request)
    {
        auth()->loginUsingId($request->input('user_id'));

        return response()->json([
            'redirect_to' => url('/'),
        ]);
    }
}

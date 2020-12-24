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
        $loginRequest = $request->getLoginRequest();

        auth()->loginUsingId($loginRequest->user_id);

        $loginRequest->delete();

        return redirect()->to('/');
    }
}

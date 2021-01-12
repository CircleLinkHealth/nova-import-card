<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

class UserSettingsController extends Controller
{
    public function show()
    {
        $user = auth()->user()
            ->load([
                'authyUser',
            ]);

        $user->global_role = $user->practiceOrGlobalRole();

        return view('user-settings')
            ->with('user', $user);
    }
}

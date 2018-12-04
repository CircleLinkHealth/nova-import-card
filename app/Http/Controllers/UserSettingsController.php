<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

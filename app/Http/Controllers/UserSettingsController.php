<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserSettingsController extends Controller
{
    public function show()
    {
        $user = auth()->user()
                      ->load(['authyUser']);

        return view('user-settings')
            ->with('user', $user);
    }
}

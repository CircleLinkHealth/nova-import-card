<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;

class ImpersonationController extends Controller
{
    public function postImpersonate(Request $request)
    {
        $email = $request->input('email');

        try {
            $user = User::whereEmail($email)->firstOrFail();
        } catch (\Exception $e) {
            echo "No User was found with email address ${email} <br><br> Please go back and try again.";
            exit;
        }

        auth()->setUserToImpersonate($user);

        return redirect()->route('patients.dashboard', ['impersonatedUserEmail' => $email]);
    }
}

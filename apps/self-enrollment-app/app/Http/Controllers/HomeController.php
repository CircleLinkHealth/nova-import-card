<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function selfEnrollmentNova()
    {
        return redirect(url('/superadmin/resources/self-enrollment-metrics-resources'));
    }

    public function showHomepage()
    {
        if ( ! auth()->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = auth()->user();

        if ($user->isSurveyOnly()) {
            auth()->logout();

            return redirect()->back();
        }

        if ($user->isAdmin()) {
            return $this->selfEnrollmentNova();
        }

        Log::error("User $user->id should not have reached here!");

        return back();
    }
}

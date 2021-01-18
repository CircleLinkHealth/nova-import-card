<?php

namespace App\Http\Controllers;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function showHomepage() {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = auth()->user();

//        if ($user->isSurveyOnly()) {
//            return redirect()->route('login-enrollment-survey');
//        }

        if ($user->isAdmin()){
            return $this->selfEnrollmentNova();
        }

        Log::error("User $user->id should not have reached here!");
        return back();


    }

    public function selfEnrollmentNova()
    {
        return redirect(url("/superadmin/resources/self-enrollment-metrics-resources"));
    }
}

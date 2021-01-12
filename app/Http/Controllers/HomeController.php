<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function showHomepage() {
        if (auth()->guest()) {
            return redirect()->route('login');
        }

        return $this->selfEnrollmentNova();
    }

    public function selfEnrollmentNova()
    {
        if (auth()->user()->isAdmin()){
            return redirect(url("/superadmin/resources/self-enrollment-metrics-resources"));
        }
    }
}

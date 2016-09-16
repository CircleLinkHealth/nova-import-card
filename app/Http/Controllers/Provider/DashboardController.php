<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function getIndex(){
        return view('provider.dashboard');
    }

    public function getCreateUser()
    {
        return view('provider.user.create');
    }
}

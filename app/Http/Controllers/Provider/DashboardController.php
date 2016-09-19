<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function getCreateLocation()
    {
        return view('provider.location.create');
    }

    public function getCreatePractice()
    {
        return view('provider.practice.create');
    }

    public function getCreateUser()
    {
        return view('provider.user.create');
    }

    public function getIndex()
    {
        return view('provider.layouts.dashboard');
    }

    public function postStoreUser()
    {
        return redirect()->route('get.provider.dashboard');
    }

}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (auth()->guest()) {
            return redirect()->to(config('core.apps.cpm-provider.url'));
        }

        $user = auth()->user();

        if ($user->isAdmin()) {
            return view('cpm-admin::dashboard');
        }

        if ($user->isCallbacksAdmin()) {
            return redirect()->route('patientCallManagement.v2.index');
        }

        return redirect()->to(config('core.apps.cpm-provider.url'));
    }
}

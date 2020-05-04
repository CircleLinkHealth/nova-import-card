<?php

namespace App\Http\Controllers;

use App\User;

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
        /** @var User $user */
        $user = auth()->user();
        if ($user->hasRole('participant')) {
            return view('home');
        }

        return \App::call('App\Http\Controllers\PatientController@index');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
        if (auth()-> guest() || ! auth()->user()->isAdmin()) {
            return redirect()->to(config('core.apps.cpm-provider.url'));
        }
        
        return view('cpm-admin::dashboard');
    }
}

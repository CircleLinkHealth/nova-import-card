<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\LoginController;

class WelcomeController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Welcome Controller
    |--------------------------------------------------------------------------
    |
    | This controller renders the "marketing page" for the application and
    | is configured to only allow guests. Like most of the other sample
    | controllers, you are free to modify or remove it as you desire.
    |
    */

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Show the application welcome screen to the user.
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function index()
    {
        if ( ! auth()->check()) {
            return app(LoginController::class)->showLoginForm();
        }
    
        $user = auth()->user();

        if ($user->roles->isEmpty()) {
            auth()->logout();

            throw new \Exception("Log in for User with id {$user->id} failed. User has no assigned Roles.");
        }

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard', []);
        }

        if ($user->hasRole('saas-admin')) {
            return redirect()->route('saas-admin.home', []);
        }

        if ($user->hasRole('care-ambassador') || $user->hasRole('care-ambassador-view-only')) {
            return redirect()->route('enrollment-center.dashboard');
        }

        if ($user->hasRole('ehr-report-writer')) {
            if ( ! app()->environment('production')) {
                return redirect()->route('report-writer.dashboard');
            }

            return redirect()->route('login')->with(['messages' => ['message' => 'Ehr Report Writers can only login in the Worker. Please visit: https://circlelink-worker.medstack.net']]);
        }

        return redirect()->route('patients.dashboard', []);
    }
}

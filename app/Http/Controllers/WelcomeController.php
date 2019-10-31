<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
    public function index(Request $request)
    {
        if ( ! auth()->check()) {
            return \App::call('App\Http\Controllers\Auth\LoginController@showLoginForm');
        }

        $user = auth()->user();

        if ($user->roles->isEmpty()) {
            auth()->logout();

            throw new \Exception("Log in for User with id {$user->id} failed. User has no assigned Roles.");
        }

        if ($user->isAdmin()) {
            return \App::call('App\Http\Controllers\Admin\DashboardController@index');
        }

        if ($user->hasRole('saas-admin')) {
            return \App::call('App\Http\Controllers\Patient\PatientController@showDashboard');
        }

        if ($user->hasRole('care-ambassador') || $user->hasRole('care-ambassador-view-only')) {
            return \App::call('App\Http\Controllers\Enrollment\EnrollmentCenterController@dashboard');
        }

        if ($user->hasRole('ehr-report-writer')) {
            if ( ! isProductionEnv()) {
                return \App::call('App\Http\Controllers\EhrReportWriterController@index');
            }

            return redirect()->route('login')->with(
                ['messages' => ['message' => 'Ehr Report Writers can only login in the Worker. Please visit: https://circlelink-worker.medstack.net']]
            );
        }

        if ($user->hasRole('participant')) {
            return \App::call('App\Http\Controllers\ReportsController@viewPatientCareplan');
        }

        return \App::call('App\Http\Controllers\Patient\PatientController@showDashboard');
    }
}

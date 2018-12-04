<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Contracts\Auth\Guard;

class PatientProgramSecurity
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function handle(
        $request,
        Closure $next
    ) {
        $loggedInUser = auth()->user();

        if ( ! $loggedInUser) {
            return redirect()->guest('login');
        }

        if ($loggedInUser->hasRole('care-ambassador')) {
            return redirect()->route('enrollment-center.dashboard', []);
        }

        if ($loggedInUser->hasRole('ehr-report-writer')) {
            return redirect()->route('report-writer.dashboard', []);
        }

        if ($request->route()->patientId) {
            $patient = User::whereId($request->route()->patientId)
                ->with('practices')
                ->has('patientInfo')
                ->first();

            if ( ! $patient) {
                return response('Could not locate patient.', 401);
            }
            if ($patient->id == $loggedInUser->id && ! $loggedInUser->hasPermission('users-view-self')) {
                abort(403);
            }
            if ($patient->id != $loggedInUser->id && ! $loggedInUser->hasPermission('users-view-all')) {
                abort(403);
            }
            if (
                    0 == count(array_intersect(
                        $patient->practices->pluck('id')->all(),
                        auth()->user()->practices->pluck('id')->all()
                    ))
                ) {
                abort(403);
            }
        }

        return $next($request);
    }
}

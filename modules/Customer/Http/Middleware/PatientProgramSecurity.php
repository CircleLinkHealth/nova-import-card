<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Http\Middleware;

use CircleLinkHealth\Customer\Entities\User;
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

        if ($loggedInUser->isParticipant()) {
            abort(403);
        }

        if ($loggedInUser->hasRole('care-ambassador')) {
            return redirect()->route('enrollment-center.dashboard', []);
        }

        if ($loggedInUser->hasRole('ehr-report-writer')) {
            return redirect()->route('report-writer.dashboard', []);
        }

        $patientId = $request->route()->parameter('patientId');

        if ($patientId) {
            $patient = \Cache::remember("user_{$loggedInUser->id}_can_see_patient_$patientId", 2, function () use ($patientId, $loggedInUser) {
                return User::whereId($patientId)
                    ->intersectPracticesWith($loggedInUser)
                    ->has('patientInfo')
                    ->exists();
            });

            if ( ! $patient) {
                return response('Could not locate patient.', 401);
            }

            if ($patientId == $loggedInUser->id && ! $loggedInUser->hasPermission('users-view-self')) {
                abort(403);
            }
            if ($patientId != $loggedInUser->id && ! $loggedInUser->hasPermission('users-view-all')) {
                abort(403);
            }
        }

        return $next($request);
    }
}
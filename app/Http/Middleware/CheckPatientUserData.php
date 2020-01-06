<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Middleware;

use Closure;

class CheckPatientUserData
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $loggedUser = auth()->user();

        if ( ! $loggedUser->hasRole('participant')) {
            auth()->logout();

            return redirect('login')->withErrors(['This page can be accessed only by patients.']);
        }

        if ( ! $loggedUser->carePlan) {
            /** @var RaygunLogger $raygunLogger */
            $raygunLogger = app('raygun.logger');
            if ($raygunLogger) {
                $raygunLogger->error("Care Plan for patient user with id: {$loggedUser->id} not found");
            }
            auth()->logout();

            return redirect('login')->withErrors(['careplan-error' => "[402] There was an error retrieving your Care Plan and we are investigating the issue. <br> If the problem persists, please contact CircleLink Health Support at <a href='mailto:contact@circlelinkhealth.com'>contact@circlelinkhealth.com</a>."]);
        }

        return $next($request);
    }
}

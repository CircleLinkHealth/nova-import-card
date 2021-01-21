<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Middleware;

use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Closure;

class CheckPatientUserData
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $loggedUser = auth()->user();

        if ( ! $loggedUser->isParticipant()) {
            auth()->logout();

            return redirect('login')->withErrors(['This page can be accessed only by patients.']);
        }

        if ( ! patientLoginIsEnabledForPractice($loggedUser->program_id)) {
            auth()->logout();

            return redirect('login')->withErrors(['This feature has not been enabled by your Provider yet.']);
        }

        if ( ! $loggedUser->carePlan) {
            \Log::channel('sentry')->error("Care Plan for patient user with id: {$loggedUser->id} not found");
            \Log::error("Care Plan for patient user with id: {$loggedUser->id} not found");

            auth()->logout();

            return redirect('login')->withErrors(['careplan-error' => "[402] There was an error retrieving your Care Plan and we are investigating the issue. <br> If the problem persists, please contact CircleLink Health Support at <a href='mailto:contact@circlelinkhealth.com'>contact@circlelinkhealth.com</a>."]);
        }

        if (in_array($loggedUser->carePlan->status, [CarePlan::DRAFT, CarePlan::QA_APPROVED])) {
            auth()->logout();

            return redirect('login')->withErrors(['careplan-error' => "Your Care Plan is being reviewed. <br> For details, please contact CircleLink Health Support at <a href='mailto:contact@circlelinkhealth.com'>contact@circlelinkhealth.com</a>."]);
        }

        return $next($request);
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Middleware;

use App\Services\SurveyInvitationLinksService;
use App\Survey;
use App\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param string|null              $guard
     *
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ( ! Auth::guard($guard)->check()) {
            Log::debug('RedirectIfAuthenticated Middleware -> route login');

            return $next($request);
        }

        Log::debug('RedirectIfAuthenticated -> ready to route somewhere');

        // NOTE:
        // I am not happy with this implementation.
        // Redirects should be done in a different way.
        // Now, any new route has to be inserted in this middleware,
        // otherwise, user can only go to surveys, and main page

        if (Route::is('enrollee.show.logout.success')) {
            return $next($request);
        }

        /** @var User $user */
        $user          = auth()->user();
        $isParticipant = $user->hasRole('participant');

        $isSignedLogin = Route::is('auth.login.signed');
        $isHome        = Route::is('home');

        if ( ! $isSignedLogin) {
            if ($isParticipant) {
                Log::debug('RedirectIfAuthenticated -> not a signed login, user is patient and redirecting going home');

                //show a welcome message and ask patient to open AWV with the link provided
                return redirect()->route('home');
            }
            Log::debug('RedirectIfAuthenticated -> not a signed login, user is not patient and redirecting going to patient list');

            return redirect()->route('patient.list');
        }

        $patientId = SurveyInvitationLinksService::getPatientIdFromSignedUrl($request->url());
        if ($isParticipant && $patientId != $user->id) {
            Log::debug('RedirectIfAuthenticated -> patient id does not match auth user id, aborting with 401');
            abort(401);
        }

        $surveyId = SurveyInvitationLinksService::getSurveyIdFromSignedUrl($request->url());
        $name     = Survey::find($surveyId, ['name'])->name;

        if (Survey::HRA === $name) {
            Log::debug('RedirectIfAuthenticated -> redirecting to HRA');

            return redirect()
                ->route(
                    'survey.hra',
                    [
                        'patientId' => $patientId,
                        'surveyId'  => $surveyId,
                    ]
                );
        }
        if (Survey::ENROLLEES === $name) {
            Log::debug('RedirectIfAuthenticated -> redirecting to Enrollees Survey');

            return redirect()->route(
                'survey.enrollees',
                [
                    'patientId' => $user->id,
                    'surveyId'  => $surveyId,
                ]
            );
        }
        if (Survey::VITALS === $name) {
            if ($isParticipant) {
                Log::debug('RedirectIfAuthenticated -> user is patient, redirecting to Vitals - Not Authorized');
                //should not reach here because it will be stopped from the permissions middleware,
                //see web.php
                return redirect()
                    ->route(
                        'survey.vitals.not.authorized',
                        [
                            'patientId' => $patientId,
                        ]
                    );
            }
            Log::debug('RedirectIfAuthenticated -> user is not patient, redirecting to Vitals');

            return redirect()
                ->route(
                    'survey.vitals',
                    [
                        'patientId' => $patientId,
                    ]
                );
        }
        if ($isParticipant) {
            Log::debug('RedirectIfAuthenticated -> user is patient, redirecting to home');
            //show a welcome message and ask patient to open AWV with the link provided
            return redirect()->route('home');
        }
        Log::debug('RedirectIfAuthenticated -> user is not patient, redirecting to patient list');

        return redirect()->route('patient.list');
    }
}
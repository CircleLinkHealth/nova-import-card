<?php

namespace App\Http\Middleware;

use App\Services\SurveyInvitationLinksService;
use App\Survey;
use App\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     *
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ( ! Auth::guard($guard)->check()) {
            return $next($request);
        }

        /** @var User $user */
        $user = auth()->user();
        $isParticipant = $user->hasRole('participant');

        $isSignedLogin = Route::is('auth.login.signed');

        if ( ! $isSignedLogin) {
            if ($isParticipant) {
                //show a welcome message and ask patient to open AWV with the link provided
                return redirect()->route('home');
            } else {
                return redirect()->route('patient.list');
            }
        }

        $surveyId = SurveyInvitationLinksService::getSurveyIdFromSignedUrl($request->url());
        $name     = Survey::find($surveyId, ['name'])->name;
        $patientId = $isParticipant ? $user->id : SurveyInvitationLinksService::getPatientIdFromSignedUrl($request->url());

        if (Survey::HRA === $name) {
            return redirect()
                ->route('survey.hra',
                    [
                        'patientId' => $patientId,
                        'surveyId'  => $surveyId,
                    ]);
        } else if (Survey::VITALS === $name) {
            if ($isParticipant) {
                //should not reach here because it will be stopped from the permissions middleware,
                //see web.php
                return redirect()
                    ->route('survey.vitals.not.authorized',
                        [
                            'patientId' => $patientId,
                        ]);
            }
            else {
                return redirect()
                    ->route('survey.vitals',
                        [
                            'patientId' => $patientId,
                        ]);
            }

        } else {
            if ($isParticipant) {
                //show a welcome message and ask patient to open AWV with the link provided
                return redirect()->route('home');
            } else {
                return redirect()->route('patient.list');
            }
        }
    }
}

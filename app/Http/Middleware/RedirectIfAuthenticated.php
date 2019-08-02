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
        if (Auth::guard($guard)->check()) {

            /** @var User $user */
            $user = auth()->user();

            if ($user->hasRole('participant')) {

                if (Route::is('auth.login.signed')) {

                    $surveyId = SurveyInvitationLinksService::getSurveyIdFromSignedUrl($request->url());
                    $name     = Survey::find($surveyId, ['name'])->name;

                    if (Survey::HRA === $name) {
                        return redirect()
                            ->route('survey.hra',
                                [
                                    'patientId' => $user->id,
                                    'surveyId'  => $surveyId,
                                ]);
                    } else if (Survey::VITALS === $name) {
                        return redirect()
                            ->route('survey.vitals.not.authorized',
                                [
                                    'patientId' => $user->id,
                                ]);
                    }
                }

                //show a welcome message and ask patient to open AWV with the link provided
                return redirect()->route('home');
            }

            return redirect()->route('patient.list');
        }

        return $next($request);
    }
}

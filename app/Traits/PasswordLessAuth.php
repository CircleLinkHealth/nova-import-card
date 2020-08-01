<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

use App\PasswordlessLoginToken;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait PasswordLessAuth
{
    /**
     * Handle a login request to the application.
     *
     * @param $token
     *
     * @throws ValidationException
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response|void
     */
    public function passwordlessLogin(Request $request, $token)
    {
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }
        if ($this->attemptPasswordlessLogin($token, $request)) {
            return $this->redirectToPatientCarePlan($request);
        }
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param string  $token
     * @param Request $request
     *
     * @throws \Exception
     */
    protected function attemptPasswordlessLogin($token): bool
    {
        $token = PasswordlessLoginToken::with('user')->has('user')->whereToken($token)->first();

        if ( ! $token) {
            return false;
        }

        if ($token->user instanceof User) {
            $token->delete();

            $this->guard()->login($token->user);

            return true;
        }

        return false;
    }

    protected function redirectToPatientCarePlan(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        return $this->authenticated($request, $this->guard()->user())
            ?: redirect()->intended(route('patient.careplan.print', [$request->route('patientId')]));
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\PatientSession;
use Closure;
use Illuminate\Http\Request;

class ClearPatientSessions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @return mixed
     */
    public function handle(
        $request,
        Closure $next
    ) {
        $this->clearPatientSession($request);

        return $next($request);
    }

    /**
     * Determine whether to clear the patient session or not and then clear it.
     *
     * @param Request $request
     */
    protected function clearPatientSession(Request $request)
    {
        $patientId = $request->route('patientId') ?? $request->input('patientId');

        if ($request->method() != 'GET') {
            return;
        }

        $clearPatientSessions = preg_match('/(?<![0-9])[0-9]{2,4}(?![0-9])/', $request->getRequestUri()) == 0;

        if (!empty($patientId)) {
            $clearPatientSessions = !str_contains(
                $request->getRequestUri(),
                $patientId
            )//    && str_contains(\URL::previous(), $patientId)
            ;
        }

        if ($clearPatientSessions) {
            if (auth()->check()) {
                $user = auth()->user()->id;
            } else {
                $user = $request->input('providerId');
            }

            $session = PatientSession::where('user_id', '=', $user)
                ->delete();
        }
    }
}

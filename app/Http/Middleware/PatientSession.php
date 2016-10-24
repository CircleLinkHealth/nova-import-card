<?php

namespace App\Http\Middleware;

use App\Exceptions\HasPatientTabOpenException;
use Closure;

class PatientSession
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

        if (empty($request->route('patientId'))) {
            return $next($request);
        }

        $patientId = $request->route('patientId');

        $userId = auth()->user()->ID;

        if ($request->has('clearSession')) {
            \Session::remove('inOpenSessionWithPatientId');
        }

        if (!\Session::has('inOpenSessionWithPatientId')) {
            \Session::put('inOpenSessionWithPatientId', $patientId);
        }

        if (\Session::get('inOpenSessionWithPatientId') != $patientId) {
            throw new HasPatientTabOpenException();
        }

        return $next($request);
    }
}

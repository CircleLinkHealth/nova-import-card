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

        $user = auth()->user();

        if ($user->hasRole(['administrator'])) {
            return $next($request);
        }

        if ($request->has('clearSession')) {
            \Session::remove('inOpenSessionWithPatientId');

            return redirect()->to($request->url());
        }

        if (!\Session::has('inOpenSessionWithPatientId')) {
            \Session::put('inOpenSessionWithPatientId', $patientId);
        }

        if (
            \Session::get('inOpenSessionWithPatientId') != $patientId
            && $request->method() == 'GET'
        ) {
            throw new HasPatientTabOpenException();
        }

        return $next($request);
    }
}

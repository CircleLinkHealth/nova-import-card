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
            \App\Models\PatientSession::where('user_id', '=', $user->ID)
                ->delete();
        }

        $sessions = \App\Models\PatientSession::where('user_id', '=', $user->ID)
            ->get();

        if ($sessions->isEmpty()) {
            \App\Models\PatientSession::create([
                'user_id'    => $user->ID,
                'patient_id' => $patientId,
            ]);
        }

        $exists = \App\Models\PatientSession::where('user_id', '=', $user->ID)
            ->where('patient_id', '!=', $patientId)
            ->exists();


        if ($exists) {
            throw new HasPatientTabOpenException();
        }

        return $next($request);
    }
}

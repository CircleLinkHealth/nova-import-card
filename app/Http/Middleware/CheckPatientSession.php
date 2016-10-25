<?php

namespace App\Http\Middleware;

use App\Exceptions\HasPatientTabOpenException;
use App\Models\PatientSession;
use Closure;

class CheckPatientSession
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

        if ($request->has('clearSession') && $request->method() == 'GET') {
            PatientSession::where('user_id', '=', $user->ID)
                ->delete();

            PatientSession::create([
                'user_id'    => $user->ID,
                'patient_id' => $patientId,
            ]);

            return redirect()->to($request->url());
        }

        $sessions = PatientSession::where('user_id', '=', $user->ID)
            ->get();

        if ($sessions->isEmpty()) {
            PatientSession::create([
                'user_id'    => $user->ID,
                'patient_id' => $patientId,
            ]);
        }

        $exists = PatientSession::where('user_id', '=', $user->ID)
            ->where('patient_id', '!=', $patientId)
            ->exists();


        if ($exists) {
            throw new HasPatientTabOpenException();
        }

        return $next($request);
    }
}

<?php

namespace CircleLinkHealth\Customer\Middleware;

use CircleLinkHealth\Customer\Entities\User;
use Closure;
use Illuminate\Http\Request;

class PatientHasParticipantRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $patientId = $request->route()->parameter('patientId');

        if (is_null($patientId)){
            throw new \Exception("Patient ID not found in route.");
        }

        $isParticipant = User::with(['roles', 'permissions'])
                             ->findOrFail($patientId)
                             ->isParticipant();

        if (! $isParticipant){
            throw new \Exception("Patient (ID: $patientId), does not have a participant role. This will cause multiple bugs in the system. Please fix immediately.");
        }

        return $next($request);
    }
}

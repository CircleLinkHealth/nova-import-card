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
            return response('Something went wrong with loading this page for this patient. Please contact CLH Support.');
        }

        $isParticipant = User::with(['roles'])
                             ->findOrFail($patientId)
                             ->isParticipant();

        if (! $isParticipant) {
            return response('There is an error with this patient. Please contact CLH support.');
        }

        return $next($request);
    }
}

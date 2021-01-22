<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Middleware;

use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Closure;

class CheckCarePlanMode
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $patientId = $request->route()->patientId;

        $cp = CarePlan::where('user_id', '=', $patientId)
            ->first();

        if ($cp && CarePlan::WEB == $cp->mode) {
            return $next($request);
        }

        return redirect()->route('patient.summary', ['patientId' => $patientId]);
    }
}

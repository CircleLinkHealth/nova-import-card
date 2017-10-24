<?php

namespace App\Http\Middleware;

use App\CarePlan;
use Closure;

class CheckCarePlanMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $patientId = $request->route()->patientId;

        $cp = CarePlan::where('user_id', '=', $patientId)
            ->first();

        if ($cp->mode == CarePlan::WEB) {
            return $next($request);
        }

        return redirect()->route('patient.summary', ['patientId' => $patientId]);
    }
}

<?php namespace App\Http\Middleware;

use App\User;
use Auth;
use Closure;
use Illuminate\Contracts\Auth\Guard;

class PatientProgramSecurity
{

    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;


    /**
     * Create a new filter instance.
     *
     * @param  Guard $auth
     *
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function handle(
        $request,
        Closure $next
    ) {
        if (auth()->guest()) {
            return redirect()->guest('login');
        }

        if (auth()->user()->hasRole('care-ambassador')) {
            return redirect()->route('enrollment-center.dashboard', [])->send();
        }

        if ($request->route()->patientId) {
            $user = User::whereId($request->route()->patientId)
                        ->has('patientInfo')
                        ->first();

            if ( ! $user) {
                return response('Could not locate patient.', 401);
            } else {
                if ($user->id == Auth::user()->id && ! Auth::user()->hasPermission('users-view-self')) {
                    abort(403);
                }
                if ($user->id != Auth::user()->id && ! Auth::user()->hasPermission('users-view-all')) {
                    abort(403);
                }
                if (
                    count(array_intersect(
                        $user->practices->pluck('id')->all(),
                        auth()->user()->practices->pluck('id')->all()
                    )) == 0
                ) {
                    abort(403);
                }
            }
        }

        return $next($request);
    }
}

<?php namespace App\Http\Middleware;

use App\Models\PatientSession;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

class Authenticate extends \Illuminate\Auth\Middleware\Authenticate
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
     * @param Auth|Guard $auth
     *
     */
    public function __construct(Auth $auth)
    {
        parent::__construct($auth);

        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string[] ...$guards
     *
*@return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle(
        $request,
        Closure $next,
        ...$guards
    ) {
        //Enable this when we figure out how to handle ajax requests to web
//        parent::handle($request, $next, $guards);

        // ensure user->access_disabled and user->status are passable
        if (!$this->auth->guest()) {
            if (auth()->user()) {
                if (auth()->user()->status == 'Inactive' || auth()->user()->access_disabled == 1) {
                    auth()->logout();
                    session()->flush();

                    return redirect()->route('login', [])
                        ->withErrors(['Account access disabled'])
                        ->send();
                }
            }
        }

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
            $clearPatientSessions = !str_contains($request->getRequestUri(),
                $patientId)//    && str_contains(\URL::previous(), $patientId)
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

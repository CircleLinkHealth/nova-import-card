<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SurveyInvitationLinksService;
use App\User;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class PatientLoginController extends Controller
{
    use RedirectsUsers, ThrottlesLogins;

    /**
     * @var SurveyInvitationLinksService
     */
    private $service;

    /**
     * Create a new controller instance.
     *
     * @param SurveyInvitationLinksService $service
     */
    public function __construct(SurveyInvitationLinksService $service)
    {
        $this->middleware('guest')->except('logout');
        $this->service = $service;
    }

    /**
     * Show the login form for patients.
     *
     * @param Request $request
     * @param $userId
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm(Request $request, $userId)
    {
        $urlWithToken = $request->getRequestUri();

        $user            = User::with(['primaryPractice', 'billingProvider'])->where('id', '=', $userId)->firstOrFail();
        $practiceName    = $user->getPrimaryPracticeName();
        $doctorsLastName = $user->billingProviderUser()->display_name;

        return view('surveyUrlAuth.surveyLoginForm',
            compact('userId', 'urlWithToken', 'practiceName', 'doctorsLastName'));
    }

    /**
     * Handle a login request to the application.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'birth_date'      => 'required|date',
            'url'             => 'required|string',
        ]);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request), $request->filled('remember')
        );
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  Request $request
     *
     * @return array
     */
    protected function credentials(Request $request)
    {
        $input = $request->only($this->username(), 'birth_date', 'url');

        return [
            'name'         => $input[$this->username()],
            'signed_token' => $this->service->parseUrl($input['url']),
            'dob'          => Carbon::parse($input['birth_date'])->startOfDay(),
        ];
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  Request $request
     *
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        return $this->authenticated($request, $this->guard()->user())
            ?: redirect()->intended($this->redirectPath());
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  mixed $user
     *
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        //
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @param  Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return $this->loggedOut($request)
            ?: redirect('/');
    }

    /**
     * The user has logged out of the application.
     *
     * @param  Request $request
     *
     * @return mixed
     */
    protected function loggedOut(Request $request)
    {
        //
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    protected function redirectTo()
    {
        $prevUrl = Session::previousUrl();
        if (empty($prevUrl)) {
            return '/home';
        }

        $parsed   = parse_url($prevUrl);
        $path = explode('/', $parsed['path']);
        $surveyId = end($path);

        return route('survey.hra',
            [
                'practiceId' => auth()->user()->program_id,
                'patientId'  => auth()->id(),
                'surveyId'   => $surveyId,
            ]);
    }

    protected function username()
    {
        return 'name';
    }

}

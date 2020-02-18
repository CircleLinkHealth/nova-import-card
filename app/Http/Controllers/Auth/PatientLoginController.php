<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SurveyInvitationLinksService;
use App\Survey;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class PatientLoginController extends Controller
{
    use AuthenticatesUsers {
        username as traitUsername;
        credentials as traitCredentials;
        validateLogin as traitValidateLogin;
        showLoginForm as traitShowLoginForm;
    }

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

        $user         = User::with(['primaryPractice', 'billingProvider'])->where('id', '=', $userId)->firstOrFail();
        $practiceName = $user->getPrimaryPracticeName();

        $doctor          = $user->billingProviderUser();
        $doctorsLastName = "???";
        if ($doctor) {
            $doctorsLastName = $doctor->display_name;
        }

        return view('surveyUrlAuth.surveyLoginForm',
            compact('userId', 'urlWithToken', 'practiceName', 'doctorsLastName'));
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
        Log::debug('PatientLoginController->redirectTo');

        /** @var User $user */
        $user = auth()->user();

        $prevUrl = Session::previousUrl();
        if (empty($prevUrl) || ! $user->hasRole('participant')) {
            Log::debug("PatientLoginController: no prevUrl or no participant [$user->id]. Going `home`");
            return route('home');
        }

        $surveyId = $this->service->getSurveyIdFromSignedUrl($prevUrl);
        $name     = Survey::find($surveyId, ['name'])->name;

        if (Survey::HRA === $name) {
            Log::debug('PatientLoginController: should redirect to HRA');
            $route = route('survey.hra',
                [
                    'patientId' => $user->id,
                    'surveyId'  => $surveyId,
                ]);
        } else {
            Log::debug('PatientLoginController: should redirect to Vitals - Not Authorized');
            $route = route('survey.vitals.not.authorized',
                [
                    'patientId' => $user->id,
                ]);
        }

        //make sure url.intended is not set
        Session::pull('url.intended');

        return $route;
    }

    protected function username()
    {
        return 'name';
    }

}

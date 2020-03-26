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
     * @param Request $request
     * @param $userId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showLoginForm(Request $request, $userId)
    {
        $loginFormData = $this->getLoginData($request, $userId);
        $urlWithToken = $loginFormData['urlWithToken'];
        $practiceName = $loginFormData['practiceName'];
        $doctorsLastName = $loginFormData['doctorsLastName'];
        $loginScreenTitle = $this->loginFormTitle($userId);

        return view('surveyUrlAuth.surveyLoginForm',
            compact('userId', 'urlWithToken', 'practiceName', 'doctorsLastName', "loginScreenTitle"));
    }

    /**
     * @param $request
     * @param $userId
     * @return array
     */
    public function getLoginData($request, $userId)
    {
        $user = User::with(['primaryPractice', 'billingProvider'])->where('id', '=', $userId)->firstOrFail();
        $doctor = $user->billingProviderUser();
        $doctorsLastName = "???";
        if ($doctor) {
            $doctorsLastName = $doctor->display_name;
        }

        return [
            'urlWithToken' => $request->getRequestUri(),
            'user' => $user,
            'practiceName' => $user->getPrimaryPracticeName(),
            'doctor' => $doctor,
            'doctorsLastName' => $doctorsLastName,
        ];
    }

    /**
     * @param $userId
     * @return string
     */
    public function loginFormTitle($userId)
    {
        $survey = Survey::whereName('Enrollees')->select('id')->first();
        $isEnrolleSurvey = false;
        if (!empty($survey)) {
            $isEnrolleSurvey = $survey->users()->where('user_id', $userId)->wherePivot('survey_id', $survey->id)->exists();
        }
        // This supposed to add a <br> but it does not
        return $isEnrolleSurvey
            ? 'Almost done! Just need some information'
            : wordwrap("Annual Wellness Survey Login", 8, "\n", true);
    }

    /**
     * Validate the user login request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'birth_date' => 'required|date',
            'url' => 'required|string',
        ]);
    }

    protected function username()
    {
        return 'name';
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param \Illuminate\Http\Request $request
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
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param Request $request
     *
     * @return array
     */
    protected function credentials(Request $request)
    {
        $input = $request->only($this->username(), 'birth_date', 'url');

        return [
            'name' => $input[$this->username()],
            'signed_token' => $this->service->parseUrl($input['url']),
            'dob' => Carbon::parse($input['birth_date'])->startOfDay(),
        ];
    }

    protected function redirectTo()
    {
        /** @var User $user */
        $user = auth()->user();

        $prevUrl = Session::previousUrl();
        if ((empty($prevUrl) || !$user->hasRole('participant')) && !$user->hasRole('survey-only')) {
            return route('home');
        }

        $surveyId = $this->service->getSurveyIdFromSignedUrl($prevUrl);
        $name = Survey::find($surveyId, ['name'])->name;


        if (Survey::ENROLLEES === $name) {
            $route = route('survey.enrollees',
                [
                    'patientId' => $user->id,
                    'surveyId' => $surveyId,
                ]);
        }

        if (Survey::HRA === $name) {
            $route = route('survey.hra',
                [
                    'patientId' => $user->id,
                    'surveyId' => $surveyId,
                ]);
        }

        if (Survey::VITALS === $name) {
            $route = route('survey.vitals.not.authorized',
                [
                    'patientId' => $user->id,
                ]);
        }

        //fixme: don't know why I had to do this
        //without it, it would redirect to home page '/'
        Session::put('url.intended', $route);

        return $route;
    }
}

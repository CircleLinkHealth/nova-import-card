<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Jenssegers\Agent\Agent;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers {
        login as traitLogin;
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    protected $username = 'email';

    /**
     * Throttle login on this many unsuccessful attempts.
     *
     * @var int
     */
    protected $maxAttempts = 4;

    /**
     * Throttle logon for this many minutes after $maxAttempts failed login attempts.
     *
     * @var int
     */
    protected $decayMinutes = 5;


    /**
     * Create a new controller instance.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return $this->username;
    }


    /**
     * Overrides laravel method
     *
     * @return $this|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showLoginForm()
    {
        $agent = new Agent();

        if (! $this->validateBrowserVersion($agent) && ! optional(session('errors'))->has('invalid-browser-force-switch')) {
            $message = "You are using an outdated version of {$agent->browser()}. Please update to a newer version.";

            return view('auth.login')->withErrors(['outdated-browser' => [$message]]);
        }

        return view('auth.login');
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function login(Request $request)
    {
        $this->usernameOrEmail($request);
        $loginResponse = $this->traitLogin($request);

        $agent = new Agent();

        $isClh = auth()->user()->hasRole(['care-center', 'administrator']);

        if (! $this->validateBrowserCompatibility($agent, $isClh)) {
            $this->sendInvalidBrowserResponse($agent->browser(), $isClh);
        }

        if (! $this->validatePasswordAge()) {
            auth()->logout();
            $days = LoginController::MIN_PASSWORD_CHANGE_IN_DAYS;

            return redirect('auth/password/reset')
                ->withErrors(['old-password' => "You password has not been changed for the last $days days. Please reset it to continue."]);
        }

        return $loginResponse;
    }

    /**
     * Determine whether log in input is email or username, and do the needful to authenticate
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function usernameOrEmail(Request $request)
    {
        if (! $request->filled('email')) {
            return false;
        }

        $request->merge(array_map('trim', $request->input()));

        if (! str_contains($request->input('email'), '@')) {
            $this->username = 'username';

            $request->merge([
                'username' => $request->input('email'),
            ]);
        }
    }

    /**
     * Check whether the user is using a supported browser.
     *
     * @return bool
     */
    protected function validateBrowserCompatibility(Agent $agent, $isCLH = false)
    {
        if (auth()->check() && auth()->user()->skip_browser_checks) {
            return true;
        }

        if ($agent->isIE()) {
            return false;
        }

        return $this->validateBrowserVersion($agent, $isCLH);
    }

    /**
     * @param $browser
     * @param bool $isCLH
     *
     * @return void
     * @throws ValidationException
     */
    protected function sendInvalidBrowserResponse($browser, $isCLH = false)
    {
        $messages = [];
        if ($browser == 'IE') {
            $messages = [
                'invalid-browser' => "I'm sorry, you may be using a version of Internet Explorer (IE) that we don't support. 
            Please use Chrome browser instead. 
            <br>If you must use IE, please use IE11 or later.
            <br>If you must use IE v10 or earlier, please e-mail <a href='mailto:contact@circlelinkhealth.com'>contact@circlelinkhealth.com</a>",
            ];
        }

        if ($isCLH) {
            auth()->logout();

            if ($browser == 'Chrome') {
                $messages = [
                    'invalid-browser-force-switch' => 'Care Coaches and Administrators are required to use a version of Chrome that is less than 6 months old. Please update to a newer version of Chrome and try logging in again.',
                ];
            } else {
                $messages = [
                    'invalid-browser-force-switch' => 'Care Coaches and Administrators are required to use a version of Chrome that is less than 6 months old. Please switch to Chrome and try logging in again.',
                ];
            }
        }

        throw ValidationException::withMessages($messages);
    }

    /**
     * @param Request $request
     */
    protected function storeBrowserCompatibilityCheckPreference(Request $request)
    {
        if (! auth()->check() || auth()->user()->hasRole('care-center')) {
            return;
        }

        auth()->user()->update([
            'skip_browser_checks' => $request->input('doNotShowAgain', false),
        ]);

        return response()->redirectTo($this->redirectPath());
    }

    /**
     * Logout due to inactivity
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function inactivityLogout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect()
            ->route('login')
            ->with([
                'messages' => [
                    'Our apologies. The page has expired due to inactivity or a user logout on a different browser tab.',
                ],
            ]);
    }

    const MIN_PASSWORD_CHANGE_IN_DAYS = 180;

    /**
     * Checks the last time the password was changed.
     * Returns true (validation success) if it was changed in less than
     * {@link LoginController::MIN_PASSWORD_CHANGE_IN_DAYS}, otherwise false.
     *
     * @return bool
     */
    protected function validatePasswordAge()
    {
        $user = auth()->user();

        //nothing to validate if not auth
        if (! $user) {
            return true;
        }

        $diffInDays = 0;
        $history    = $user->passwordsHistory;
        if ($history) {
            $diffInDays = $history->updated_at->diffInDays(Carbon::today());
        }

        return $diffInDays < LoginController::MIN_PASSWORD_CHANGE_IN_DAYS;
    }

    /**
     * @param Agent $agent
     * @param bool $isCLH
     *
     * @return bool
     */
    protected function validateBrowserVersion(Agent $agent, $isCLH = false)
    {

        //$request->cookie('skip_outdated_browser_check') -> returns null for some reason
        if (isset($_COOKIE['skip_outdated_browser_check'])) {
            return true;
        }

        $browsers = $this->getBrowsers();

        $browser = $browsers->where('name', $agent->browser())->first();

        if ($browser) {

            //if the User is CLH staff, only perform the check if the browser is Chrome, otherwise fail.
            //required_version is 6 months old
            if ($isCLH) {
                if ($browser->name == 'Chrome') {
                    $browserVersionString = $browser->required_version;
                } else {
                    return false;
                }
            } else {
                $browserVersionString = $browser->warning_version;
            }

            $browserVersion = explode(".", $browserVersionString);
            $agentVersion = explode(".", $agent->version($agent->browser()));

            return $this->checkVersion($agentVersion, $browserVersion);
        }

        return false;
    }

    /**
     * @param array $agentVersion
     * @param array $browserVersion
     *
     * @return bool
     */
    protected function checkVersion(array $agentVersion, array $browserVersion)
    {
        for ($x = 0; $x <= 4; $x++) {
            if (array_key_exists($x, $agentVersion) && array_key_exists($x, $browserVersion)) {
                if ((int)$agentVersion[$x] > (int)$browserVersion[$x]) {
                    return true;
                } elseif ((int)$agentVersion[$x] < (int)$browserVersion[$x]) {
                    return false;
                }
            }
        }

        return true;
    }


    /**
     * @return Collection
     */
    protected function getBrowsers(): Collection
    {
        return DB::table('browsers')->get();
    }
}

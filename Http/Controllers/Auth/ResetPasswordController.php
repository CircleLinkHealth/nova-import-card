<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Http\Controllers\Auth;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Entities\UserPasswordsHistory;
use CircleLinkHealth\Customer\Rules\PasswordCharacters;
use CircleLinkHealth\Customer\Traits\ManagesPatientCookies;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */
    use ManagesPatientCookies;

    use ResetsPasswords {
        reset as traitReset;
    }

    const EXPIRED_TOKEN_ERROR_MESSAGE = 'Your password reset token has expired. Please request a new one.';

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * @var \CircleLinkHealth\Customer\Entities\User
     */
    private $userToReset;

    /**
     * Create a new controller instance.
     */
    public function __construct(Request $request)
    {
        $this->middleware('guest');
    }

    /**
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function reset(Request $request)
    {
        $input        = $request->only(['email', 'password']);
        $email        = $input['email'];
        $new_password = $input['password'];

        $current_password = null;

        if ($email && $new_password) {
            $this->userToReset = $this->getUserToReset($email);
            if ( ! $this->userToReset) {
                return redirect()->back()
                    ->withErrors([
                        'email' => 'No user found with the supplied email address.',
                    ]);
            }

            $current_password = $this->userToReset->password;

            if ( ! $this->validateNewPasswordUsingPasswordsHistory($current_password, $new_password)) {
                return redirect()->back()
                    ->withInput($request->only('email'))
                    ->withErrors([
                        'email' => 'You have used this password again in the past. Please choose a different one.',
                    ]);
            }
        }

        $resetResponse = $this->traitReset($request);

        $this->changeResetResponseTargetUrlIfYouMust($resetResponse);

        //won't even reach here if traitReset fails (throws exception)
        if ( ! ($resetResponse->isClientError() || $resetResponse->isServerError()) && $current_password) {
            $this->saveOldPasswordInHistory($current_password);
        }

        return $resetResponse;
    }

    /**
     * Display the password reset view for the given token.
     *
     * If no token is present, display the link request form.
     *
     * @param string|null $token
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showResetForm(Request $request, $token = null)
    {
        //This became an issue while user was logged in as admin, then tried to reset password as patient
        //It caused the checkPracticeNameCookie to misbehave
        if (auth()->check()) {
            auth()->logout();
        }

        $this->checkPracticeNameCookie($request);

        $email = $request->input('email');

        $userIsPatient = false;

        //If a patient tries to reset their password, pre-fill email input and disable it,
        //to prevent unnecessary confusion for the patient
        if ($email) {
            $user = User::whereEmail($email)->first();

            if ($user) {
                $userIsPatient = $user->isParticipant();
                if ( ! $user->isParticipant()) {
                    $this->forgetCookie();
                }
            }
        }

        if ($userIsPatient) {
            $request->session()->flash(
                'messages',
                ['patient-user' => 'Please enter your new password below, which must be at least 8 characters, contain an uppercase letter, number and a special character (!,$,#,%,@,&,*)']
            );
        }

        return response()->view('auth.passwords.reset', [
            'token'           => $token,
            'email'           => $email,
            'user_is_patient' => $userIsPatient,
        ]);
    }

    protected function rules()
    {
        return [
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => [
                'required',
                'filled',
                'min:8',
                'confirmed',
                new PasswordCharacters(),
            ],
        ];
    }

    /**
     * Redirect to request pasword reset page if:
     * - email belongs to patient
     * - reset has failed due to expired token.
     */
    private function changeResetResponseTargetUrlIfYouMust(RedirectResponse $response)
    {
        if ( ! $this->userToReset->isParticipant()) {
            return;
        }

        $sessionAttributes = $response->getSession()->all();

        if (array_key_exists('errors', $sessionAttributes)) {
            $messages = $sessionAttributes['errors']->getMessages();

            if (array_key_exists('email', $messages)) {
                if (in_array(self::EXPIRED_TOKEN_ERROR_MESSAGE, $messages['email'])) {
                    $response->setTargetUrl(route('password.request', [
                        'email' => $this->userToReset->email,
                    ]));
                }
            }
        }
    }

    /**
     * Get User model with passwordsHistory relation.
     *
     * @return \CircleLinkHealth\Customer\Entities\User|null
     */
    private function getUserToReset(string $email)
    {
        return User::where('email', '=', $email)
            ->with('passwordsHistory')
            ->first();
    }

    private function saveOldPasswordInHistory($old_password)
    {
        $history = $this->userToReset->passwordsHistory;

        if ( ! $history) {
            $history          = new UserPasswordsHistory();
            $history->user_id = $this->userToReset->id;
        }

        $history->older_password = $history->old_password;
        $history->old_password   = $old_password;
        $history->save();
    }

    /**
     * Check new password against current password and previous two previous passwords.
     * If one of them is the same as the new password,
     * reject it.
     *
     * @param $current_password
     * @param $new_password
     *
     * @return bool
     */
    private function validateNewPasswordUsingPasswordsHistory($current_password, $new_password)
    {
        $isDiff = ! \Hash::check($new_password, $current_password);

        if ( ! $isDiff) {
            //new password is same as current password
            return $isDiff;
        }

        $history = $this->userToReset->passwordsHistory;
        if ($history) {
            if ($history->old_password) {
                $isDiff = ! \Hash::check($new_password, $history->old_password);
            }
            if ($isDiff && $history->older_password) {
                $isDiff = ! \Hash::check($new_password, $history->older_password);
            }
        }

        return $isDiff;
    }
}

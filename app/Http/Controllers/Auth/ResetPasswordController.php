<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ManagesPatientCookies;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Entities\UserPasswordsHistory;
use CircleLinkHealth\Customer\Rules\PasswordCharacters;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;

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
        $this->checkPracticeNameCookie($request);

        return response()->view('auth.passwords.reset', ['token' => $token, 'email' => $request->email]);
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

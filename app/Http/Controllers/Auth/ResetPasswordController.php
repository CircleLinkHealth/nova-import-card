<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rules\PasswordCharacters;
use App\User;
use App\UserPasswordsHistory;
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
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->middleware('guest');
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
                new PasswordCharacters,
            ],
        ];
    }

    /**
     * @var \App\User
     */
    private $userToReset;

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        $input        = $request->only(['email', 'password']);
        $email        = $input['email'];
        $new_password = $input['password'];

        $current_password = null;

        if ($email && $new_password) {
            $this->userToReset = $this->getUserToReset($email);
            if (! $this->userToReset) {
                return redirect()->back()
                                 ->withErrors([
                                     'email' => 'No user found with the supplied email address.',
                                 ]);
            }

            $current_password = $this->userToReset->password;

            if (! $this->validateNewPasswordUsingPasswordsHistory($current_password, $new_password)) {
                return redirect()->back()
                                 ->withInput($request->only('email'))
                                 ->withErrors([
                                     'email' => 'You have used this password again in the past. Please choose a different one.',
                                 ]);
            }
        }

        $resetResponse = $this->traitReset($request);

        //won't even reach here if traitReset fails (throws exception)
        if (! ($resetResponse->isClientError() || $resetResponse->isServerError()) && $current_password) {
            $this->saveOldPasswordInHistory($current_password);
        }

        return $resetResponse;
    }

    /**
     * Get User model with passwordsHistory relation.
     *
     * @param string $email
     *
     * @return \App\User|null
     */
    private function getUserToReset(string $email)
    {
        return User::where('email', '=', $email)
                   ->with('passwordsHistory')
                   ->first();
    }

    /**
     *
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

        if (! $isDiff) {
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

    private function saveOldPasswordInHistory($old_password)
    {
        $history = $this->userToReset->passwordsHistory;

        if (! $history) {
            $history          = new UserPasswordsHistory();
            $history->user_id = $this->userToReset->id;
        }

        $history->older_password = $history->old_password;
        $history->old_password   = $old_password;
        $history->save();
    }
}

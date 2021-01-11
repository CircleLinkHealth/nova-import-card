<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyAuthyTokenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user      = optional(auth()->user());
        $authyUser = optional($user->authyUser);

        return auth()->check() && $authyUser->authy_id && $authyUser->is_authy_enabled;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //we don't know what the token is, it depends on the authenticator:
            //authy -> 7 numeric digits, google authenticator: 6 numeric digits
            'token'    => 'required|digits_between:5,10',
            'is_setup' => 'sometimes|boolean',
        ];
    }
}

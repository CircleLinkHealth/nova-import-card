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
            'token' => 'required|digits:7|numeric',
        ];
    }
}

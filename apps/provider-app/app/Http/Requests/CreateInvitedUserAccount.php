<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use CircleLinkHealth\Customer\Rules\PasswordCharacters;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateInvitedUserAccount extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => [
                'required',
                'filled',
                'email',
                Rule::unique('users', 'email')->ignore($this->input('userId')),
            ],
            'first_name' => 'required|filled',
            'last_name'  => 'required|filled',
            'password'   => [
                'required',
                'filled',
                'min:8',
                new PasswordCharacters(),
            ],
            'code' => 'required|filled|exists:invites,code',
        ];
    }
}

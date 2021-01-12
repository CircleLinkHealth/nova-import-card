<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests\SAAS;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInternalUser extends FormRequest
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
            'user.username'   => ['required', 'filled', Rule::unique('users', 'username')->ignore($this->input('user.id'))],
            'user.email'      => ['required', 'filled', 'email', Rule::unique('users', 'email')->ignore($this->input('user.id'))],
            'user.id'         => 'present',
            'user.first_name' => 'required|filled',
            'user.last_name'  => 'required|filled',
            'role'            => 'required|filled',
            'practices'       => 'required|filled',
        ];
    }
}

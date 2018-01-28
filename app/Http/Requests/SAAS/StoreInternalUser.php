<?php

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
        return auth()->user()->hasRole('saas-admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user.username'   => ['required', Rule::unique('users', 'username')->ignore($this->input('user.id'))],
            'user.email'      => ['required', Rule::unique('users', 'email')->ignore($this->input('user.id'))],
            'user.id'         => 'present',
            'user.first_name' => 'required',
            'user.last_name'  => 'required',
            'role'            => 'required',
            'practices'       => 'required',
        ];
    }
}

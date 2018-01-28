<?php

namespace App\Http\Requests\SAAS;

use Illuminate\Foundation\Http\FormRequest;

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
            'user.username'   => 'required',
            'user.email'      => 'required|email|unique:users,email',
            'user.first_name' => 'required',
            'user.last_name'  => 'required',
            'role'       => 'required',
            'practices'  => 'required',
        ];
    }
}

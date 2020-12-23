<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use CircleLinkHealth\Core\Entities\AppConfig;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginFromHerokuRequest extends FormRequest
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
            'token' => [
                Rule::in([AppConfig::pull('login_from_heroku_key')]),
                'required',
                'filled',
            ],
            'user_id' => [
                Rule::exists('users', 'id'),
                'required',
                'filled',
            ],
        ];
    }
}

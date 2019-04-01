<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use CircleLinkHealth\TwoFA\Entities\AuthyUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAuthyPhoneNumber extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'country_code' => [
                'required',
                Rule::in([1, 357, 33]),
            ],
            'phone_number' => [
                'required',
                Rule::phone()->country(['US', 'CY', 'FR']),
                Rule::unique((new AuthyUser())->getTable(), 'phone_number'),
            ],
            'method' => [
                'required',
                Rule::in(['app', 'sms', 'phone']),
            ],
            'is_2fa_enabled' => 'boolean',
        ];
    }
}

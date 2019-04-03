<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use App\Rules\EligibilityPhones;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class EditEnrolleeData extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id'         => 'required',
            'first_name' => 'required|alpha_num',
            'last_name'  => 'required|alpha_num',
            'lang'       => [
                'required',
                function ($attribute, $value, $fail) {
                    if ('ES' == $value || 'EN' == $value) {
                        return true;
                    }

                    return $fail('Value for Language should be EN or ES');
                },
            ],
            'status'              => 'required',
            'phones'              => ['required', new EligibilityPhones()],
            'address'             => 'required',
            'address_2'           => 'nullable',
            'state'               => 'required',
            'city'                => 'required',
            'zip'                 => 'required|digits:5',
            'primary_insurance'   => 'required',
            'secondary_insurance' => 'nullable',
            'tertiary_insurance'  => 'nullable',
        ];
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Requests;

use CircleLinkHealth\Eligibility\Rules\EligibilityPhones;
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
            'first_name' => 'required',
            'last_name'  => 'required',
            'lang'       => 'required',
            'status'     => 'required',
            'phones'     => ['required', new EligibilityPhones()],
            'address'    => 'required',
            'address_2'  => 'nullable',
            'state'      => 'required',
            'city'       => 'required',
            'zip'        => 'required',
        ];
    }
}

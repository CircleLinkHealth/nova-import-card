<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePracticeLocation extends FormRequest
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
            'practice_id'               => 'required|exists:practices,id',
            'name'                      => 'required',
            'phone'                     => 'required|phone:US',
            'clinical_escalation_phone' => 'sometimes|phone:US',
            'fax'                       => 'phone:US',
            'address_line_1'            => 'required',
            'address_line_2'            => '',
            'city'                      => 'required',
            'state'                     => 'required',
            'timezone'                  => 'required',
            'postal_code'               => 'required|max:10',
            'emr_direct_address'        => 'nullable|email',
            'clinical_contact.email'    => 'nullable|email',
        ];
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPhones;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ContactDetailsValidator extends FormRequest
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
            'patientUserId'     => 'required',
            'phoneNumber'       => 'required|numeric',
            'agentRelationship' => 'sometimes|alpha',
            'agentName'         => 'sometimes|alpha',
            'agentEmail'        => 'sometimes|email',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $input = $validator->getData();
            if ( ! ImportPhones::validatePhoneNumber($input['phoneNumber'])) {
                $validator->errors()->add('phoneNumber', 'Phone number is not a valid US number');
            }
        });
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors()->first(), 422));
    }
}

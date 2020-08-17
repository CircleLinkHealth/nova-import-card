<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PatientAltContactGet extends FormRequest
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
            'requestIsFromCallPage' => 'required|boolean',
            'patientUserId'         => 'required',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $input = $validator->getData();
            $userId = $input['patientUserId'];

            /** @var User $patientUser */
            $patientUser = User::with('patientInfo.location', 'phoneNumbers', 'primaryPractice')
                ->where('id', $userId)
                ->first();

            ContactDetailsValidator::validateUser($patientUser, $validator);

            $this->request->add([
                'patientUser' => $patientUser,
            ]);
        });
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors()->first(), 422));
    }
}

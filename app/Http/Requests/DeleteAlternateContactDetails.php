<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use CircleLinkHealth\Customer\Entities\Patient;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class DeleteAlternateContactDetails extends FormRequest
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
            'deleteOnlyPhone' => 'sometimes|boolean',
            'patientUserId'   => 'required',
        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $input = $validator->getData();
            $userId = $input['patientUserId'];
            $patient = Patient::whereUserId($input('patientUserId'))->first();

            if (empty($patient)) {
                Log::error("Patient with user id [$userId] not found");
                $validator->errors()->add('patientUserId', "Patient with user id [$userId] not found");
            }
            $this->request->add([
                'patient' => $patient,
            ]);
        });
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors()->first(), 422));
    }
}

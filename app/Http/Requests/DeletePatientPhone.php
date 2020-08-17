<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use CircleLinkHealth\Customer\Entities\PhoneNumber;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DeletePatientPhone extends FormRequest
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
            'phoneId' => 'required',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $input = $validator->getData();
            /** @var PhoneNumber $phoneNumber */
            $phoneNumber = PhoneNumber::whereId($input['phoneId'])->first();

            if (empty($phoneNumber)) {
                $validator->errors()->add('phoneId', 'Phone number not found');
            }

            if ($phoneNumber->is_primary) {
                $validator->errors()->add('phoneId', 'You cannot delete a primary number');
            }

            $this->request->add([
                'phoneNumber' => $phoneNumber,
            ]);
        });
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors()->first(), 422));
    }
}

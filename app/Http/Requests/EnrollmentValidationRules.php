<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class EnrollmentValidationRules extends FormRequest
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
            'display_name' => 'required|string',
            'birth_date'   => 'required|date',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->inputIsInvalid($validator->getData())) {
                $validator->errors()->add('field', 'Your credentials do not match our records');
            }
        });
    }

    private function inputIsInvalid($input)
    {
        $user = User::with('patientInfo')->where('id', $input['user_id'])->firstOrFail();
        if ($user->display_name !== $input['display_name']
            || Carbon::parse($input['birth_date'])->startOfDay()->ne($user->patientInfo->birth_date)) {
            return true;
        }

        return false;
    }
}

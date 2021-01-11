<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Foundation\Http\FormRequest;

class CreateNewPatientRequest extends FormRequest
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
        if ($this->input('patientId')) {
            return [];
        }

        return [
            'first_name' => [
                'required',
                function ($attribute, $value, $fail) {
                    $count = User::whereHas('patientInfo', function ($q) {
                        $q->where('birth_date', $this->input('birth_date'));
                    })
                        ->where('first_name', $this->input('first_name'))
                        ->where('last_name', $this->input('last_name'))
                        ->where('program_id', $this->input('program_id'))
                        ->count();

                    if ($count > 0) {
                        return $fail('Patient already exists.');
                    }
                },
            ],
        ];
    }
}

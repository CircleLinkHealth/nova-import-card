<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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

        return [
            'first_name' => [
                'required',
                Rule::unique('users')->where(function ($query) {
                    $query->where('last_name', $this->input('last_name'))
                          ->where('program_id', $this->input('program_id'));
                })
                    ->ignore($this->input('patientId')),
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'first_name.unique' => 'Patient already exists.',
        ];
    }
}

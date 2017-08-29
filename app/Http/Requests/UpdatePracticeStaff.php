<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePracticeStaff extends FormRequest
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
            'practice_id'                         => 'required|exists:practices,id',
            'email'                               => 'required|email',
            'first_name'                          => 'required',
            'last_name'                           => 'required',
            'phone_number'                        => 'nullable|phone:US',
            'phone_extension'                     => 'nullable',
            'emr_direct_address'                  => 'nullable|email',
        ];
    }
}

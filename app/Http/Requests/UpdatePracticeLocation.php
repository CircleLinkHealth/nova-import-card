<?php

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
            'practice_id'    => 'required|exists:practices,id',
            'name'           => 'required',
            'phone'          => 'required',
            'address_line_1' => 'required',
            'address_line_2' => '',
            'city'           => 'required',
            'state'          => 'required',
            'timezone'       => 'required',
            'postal_code'    => 'required',
        ];
    }
}

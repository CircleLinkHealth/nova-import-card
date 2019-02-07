<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotesReport extends FormRequest
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
            'range'        => 'sometimes|integer|between:0,4',
            'getNotesFor'  => 'sometimes|array',
            'mail_filter'  => 'sometimes',
            'admin_filter' => 'sometimes',
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
            'between' => 'Something went wrong with the date you have submitted. Please select one of the options in the dropdown.',
        ];
    }
}

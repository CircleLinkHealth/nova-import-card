<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class NotesReport extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
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
            'getNotesFor'  => [
                'sometimes',
                'array',
                function ($attribute, $value, $fail) {
                    foreach ($value as $selection) {
                        $data = explode(':', $selection);
                        if (count($data) !== 2) {
                            return $fail('The value submitted for Practices/Providers select dropdown is invalid.');
                        }
                    }
                },
            ],
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

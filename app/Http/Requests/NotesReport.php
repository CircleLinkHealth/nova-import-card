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
            'range'        => 'sometimes|filled|integer|between:0,4',
            'getNotesFor'  => [
                'sometimes',
                'array',
                function ($attribute, $value, $fail) {
                    foreach ($value as $selection) {
                        $data = explode(':', $selection);
                        if (count($data) !== 2) {
                            return $fail('The value submitted for Practices/Providers select dropdown is invalid.');
                        }
                        if ($data[0] !== 'provider' && $data[0] !== 'practice'){
                            return $fail('The value submitted for Practices/Providers select dropdown is invalid.');
                        }
                        //This check is not necessary in that the system does not break, but instead fetches 0 results.
                        //The variable in check is the id of either the practice or the provider.
                        //(int)'string without number' returns 0. This check still does not successfully catch faulty input e.g. '8test'
                        if ((int)$data[1] === 0){
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

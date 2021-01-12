<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'range'       => 'sometimes|filled|integer|between:0,4',
            'getNotesFor' => [
                'sometimes',
                'array',
                function ($attribute, $value, $fail) {
                    foreach ($value as $selection) {
                        $data = explode(':', $selection);
                        if (2 !== count($data)) {
                            return $fail('The value submitted for Practices/Providers select dropdown is invalid.');
                        }

                        $selectKey = $data[0];
                        $practiceOrProviderId = $data[1];

                        if ('provider' !== $selectKey && 'practice' !== $selectKey) {
                            return $fail('The value submitted for Practices/Providers select dropdown is invalid.');
                        }
                        //This check is not necessary in that the system does not break, but instead fetches 0 results.
                        //(int)'string without number' returns 0. This check still does not successfully catch faulty input e.g. '8test'
                        if (0 === (int) $practiceOrProviderId) {
                            return $fail('The value submitted for Practices/Providers select dropdown is invalid.');
                        }
                    }
                },
            ],
            'mail_filter'  => 'sometimes',
            'admin_filter' => 'sometimes',
        ];
    }
}

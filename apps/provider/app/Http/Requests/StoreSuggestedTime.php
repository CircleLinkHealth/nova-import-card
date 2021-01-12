<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSuggestedTime extends FormRequest
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
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'suggestedFormattedTime.required' => "Please input work time value in 'hh:mm' format",
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
            'invoiceId'              => 'required|exists:nurse_invoices,id',
            'suggestedFormattedTime' => ['required', 'regex: /^(0[0-9]|1[0-9]|2[0-3]|[0-9]):[0-5][0-9]$/'],
        ];
    }
}

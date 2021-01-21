<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Circlelinkhealth\InvoicesDownload;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Http\Requests\NovaRequest;

class InvoicesDownloadRequest extends NovaRequest
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
            'date.required'           => 'Month to download invoices for is required.',
            'downloadFormat.required' => 'Invoice format is required.',
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
            'date'           => 'required',
            'downloadFormat' => 'required',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new \Exception($validator->getMessageBag()->first());
    }
}

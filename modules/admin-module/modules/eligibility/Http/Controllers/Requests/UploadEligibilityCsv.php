<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadEligibilityCsv extends FormRequest
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
            'patient_list' => [
                'required',
                'mimes:csv,txt',
            ],
            'practice_id' => 'required|numeric',
        ];
    }
}

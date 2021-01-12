<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TimeTracking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowPatientActivities extends FormRequest
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
            'selectMonth' => 'sometimes|nullable|numeric|between:1,12',
            'selectYear'  => 'sometimes|nullable|numeric|between:2000,9999',
        ];
    }
}

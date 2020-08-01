<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicationGroupMapRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'medication_group_id' => 'required|exists:cpm_medication_groups,id',
            'keyword'             => 'required|string',
        ];
    }
}

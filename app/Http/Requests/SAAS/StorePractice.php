<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests\SAAS;

use Illuminate\Foundation\Http\FormRequest;

class StorePractice extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->hasRole(['saas-admin', 'saas-admin-view-only', 'administrator']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'display_name' => 'required|filled|unique:practices,display_name',
            'service_id'   => 'required|filled',
            'amount'       => 'required|filled|numeric',
        ];
    }
}

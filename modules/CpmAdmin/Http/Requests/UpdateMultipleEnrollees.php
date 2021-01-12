<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateMultipleEnrollees extends FormRequest
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
        $rules = [
            'enrolleeIds' => 'required|array',
        ];

        if ($this->is('admin/ca-director/assign-ambassador')) {
            $rules = array_merge($rules, [
                'ambassadorId' => 'required|integer|exists:users,id',
            ]);
        }

        return $rules;
    }
}

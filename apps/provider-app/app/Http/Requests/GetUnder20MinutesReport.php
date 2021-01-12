<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class GetUnder20MinutesReport extends FormRequest
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
            'selectYear'     => 'sometimes|filled|integer|min:2000|max:2100',
            'selectMonth'    => 'sometimes|filled|integer|min:1|max:12',
            'selectPractice' => 'sometimes|integer',
        ];
    }
}

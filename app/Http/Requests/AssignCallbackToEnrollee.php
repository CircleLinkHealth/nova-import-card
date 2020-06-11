<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use App\Rules\DateEqualOrAfterUsingCarbon;
use Illuminate\Foundation\Http\FormRequest;

class AssignCallbackToEnrollee extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $x = 1;

        return [
            'care_ambassador_user_id' => 'required|exists:users,id',
            'enrollee_id'             => 'required|exists:enrollees,id',
            'callback_date'           => ['required', new DateEqualOrAfterUsingCarbon()],
            'callback_note'           => 'sometimes',
        ];
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use App\Http\Controllers\ManualCallController;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Session;

class ShowCreateManualCallForm extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Session::exists(ManualCallController::SESSION_KEY);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
        ];
    }
}

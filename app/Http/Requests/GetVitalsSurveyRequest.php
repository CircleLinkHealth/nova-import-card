<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class GetVitalsSurveyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (!Auth::check()) {
            return false;
        }

        $practiceId = $this->get('practice_id', null);

        if (empty($practiceId)) {
            return false;
        }

        return Auth::user()->hasPermissionForSite('vitals-survey-complete', $practiceId);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'patient_id'  => 'required|integer|exists:users',
            'practice_id' => 'required|integer|exists:practices',
        ];
    }
}

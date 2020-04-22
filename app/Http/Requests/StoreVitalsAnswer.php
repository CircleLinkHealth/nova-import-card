<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreVitalsAnswer extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //user should already be authenticated to reach here
        if (! Auth::check()) {
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
            'practice_id'             => 'required|integer|exists:practices,id',
            'patient_id'              => 'required|integer|exists:users,id',
            'survey_instance_id'      => 'required|integer|exists:survey_instances,id',
            'question_id'             => 'required|integer|exists:questions,id',
            'question_type_answer_id' => 'sometimes|integer|exists:question_types_answers,id',
            'value'                   => 'required',
            'survey_complete'         => 'sometimes|boolean',
        ];
    }
}

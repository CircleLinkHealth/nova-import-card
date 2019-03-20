<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreAnswer extends FormRequest
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
            'user_id'                 => 'required|integer|exists:users,id',
            'survey_instance_id'      => 'required|integer|exists:survey_instances,id',
            'question_id'             => 'required|integer|exists:questions,id',
            'question_type_answer_id' => 'sometimes|integer|exists:question_types_answers,id',
            'value_1'                 => 'required',
            'value_2'                 => 'sometimes',
        ];
    }
}

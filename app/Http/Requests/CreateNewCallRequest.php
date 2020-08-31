<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use App\Policies\CreateNoteForPatient;
use App\Rules\DateBeforeUsingCarbon;
use Illuminate\Foundation\Http\FormRequest;

class CreateNewCallRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ( ! auth()->check()) {
            return false;
        }
        if (auth()->user()->isAdmin()) {
            return true;
        }
        if ( ! auth()->user()->isCareCoach()) {
            return false;
        }
        if ( ! $patientId = $this->input('inbound_cpm_id')) {
            return false;
        }

        return app(CreateNoteForPatient::class)->can(auth()->id(), $patientId);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'type'            => 'required',
            'sub_type'        => 'required_if:type,task',
            'inbound_cpm_id'  => 'required|exists:users,id',
            'outbound_cpm_id' => '',
            'scheduled_date'  => ['required', 'after_or_equal:today', new DateBeforeUsingCarbon()],
            'window_start'    => 'required|date_format:H:i',
            'window_end'      => 'required|date_format:H:i',
            'attempt_note'    => '',
            'is_manual'       => 'required|boolean',
            'family_override' => '',
            'asap'            => '',
            'is_reschedule'   => 'sometimes|boolean',
        ];

        if (collect($this->input())->reject(fn ($item) => is_array($item))->isEmpty()) {
            return collect($rules)->transform(fn ($val, $key) => $key = "*.$key")->all();
        }

        return $rules;
    }
}

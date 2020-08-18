<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use App\Rules\DateBeforeUsingCarbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreManualScheduledCall extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return app(\App\Policies\CreateNoteForPatient::class)->can(auth()->id(), $this->route('patientId'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'suggested_date' => 'required|date',
            'date'           => ['required', 'date:after_or_equal:today', new DateBeforeUsingCarbon()],
            'window_start'   => 'required|date_format:H:i',
            'window_end'     => 'required|date_format:H:i',
            'attempt_note'   => 'sometimes',
        ];
    }
}

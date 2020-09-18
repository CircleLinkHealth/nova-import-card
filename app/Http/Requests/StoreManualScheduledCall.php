<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use CircleLinkHealth\Core\Rules\DateBeforeUsingCarbon;
use App\Rules\DateValidatorMultipleFormats;
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
        return app(\CircleLinkHealth\Customer\Policies\CreateNoteForPatient::class)->can(auth()->id(), $this->route('patientId'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $acceptedDateFormats = ['H:i', 'H:i:s'];

        return [
            'suggested_date' => 'required|date',
            'date'           => ['required', 'date:after_or_equal:today', new DateBeforeUsingCarbon()],
            'window_start'   => [
                'required',
                new DateValidatorMultipleFormats($acceptedDateFormats),
            ],
            'window_end' => [
                'required',
                new DateValidatorMultipleFormats($acceptedDateFormats),
            ],
            'attempt_note' => 'sometimes',
        ];
    }
}

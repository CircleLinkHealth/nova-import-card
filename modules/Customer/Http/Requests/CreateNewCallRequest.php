<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Http\Requests;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Policies\CreateNoteForPatient;
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
        $user = auth()->user();

        if ($user->isCareCoach()) {
            $patientId = $this->route('patientId') ?? $this->input('patientId') ?? $this->input('0.patientId');

            return app(CreateNoteForPatient::class)->can($user->id, $patientId);
        }

        return $user->hasPermission('call.create');
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

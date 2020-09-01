<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use App\Http\Requests\Traits\ValidatesNewCall;
use App\Policies\CreateNoteForPatient;
use CircleLinkHealth\Customer\Entities\User;
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
        if ( ! $patientId = collect($this->input())->pluck('inbound_cpm_id')->first()) {
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
        return [];
    }
}

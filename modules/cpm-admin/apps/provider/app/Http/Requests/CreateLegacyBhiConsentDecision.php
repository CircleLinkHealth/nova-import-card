<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateLegacyBhiConsentDecision extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $practiceId = $this->route('practiceId');

        return auth()->user()->hasPermissionForSite('legacy-bhi-consent-decision.create', $practiceId);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'decision' => [
                'required',
                Rule::in([0, 1, 2]),
            ],
        ];
    }
}

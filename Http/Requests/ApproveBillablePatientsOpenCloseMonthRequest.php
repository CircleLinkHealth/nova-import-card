<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Http\Requests;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Foundation\Http\FormRequest;

class ApproveBillablePatientsOpenCloseMonthRequest extends FormRequest
{
    public function authorize()
    {
        $practiceId = $this->input('practice_id');

        /** @var User $user */
        $user = auth()->user();

        return $user->hasPermissionForSite(['patientSummary.update'], $practiceId);
    }

    public function rules()
    {
        return [
            'practice_id' => 'required|exists:practices,id',
            'date'        => 'required|date_format:"F, Y"',
        ];
    }
}

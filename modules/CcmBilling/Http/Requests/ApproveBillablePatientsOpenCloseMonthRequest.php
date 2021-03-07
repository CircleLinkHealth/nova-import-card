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
        /** @var User $user */
        $user = auth()->user();

        return $user->hasPermission('patientSummary.update');
    }

    public function rules()
    {
        return [
            'practice_id' => 'required|exists:practices,id',
            'date'        => 'required|date_format:"F, Y"',
        ];
    }
}

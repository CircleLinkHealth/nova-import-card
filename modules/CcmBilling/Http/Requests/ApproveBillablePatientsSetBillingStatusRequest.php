<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Http\Requests;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Foundation\Http\FormRequest;

class ApproveBillablePatientsSetBillingStatusRequest extends FormRequest
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
            'report_id' => 'required|numeric',
            'status'    => 'required|string',
        ];
    }
}

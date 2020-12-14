<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Http\Controllers\Patient;

use CircleLinkHealth\Core\Contracts\DirectMail;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Http\Requests\DmCarePlanToBillingProviderRequest;
use Illuminate\Routing\Controller;

class CarePlanController extends Controller
{
    public function forwardToBillingProviderViaDM(DmCarePlanToBillingProviderRequest $request, DirectMail $dm)
    {
        $patient = User::ofType('participant')
            ->with([
                'carePlan',
                'primaryPractice.settings',
            ])
            ->find($patientId = $request->input('patient_id'));

        if ( ! $patient->carePlan) {
            return "Patient with ID $patientId does not have a CarePlan";
        }

        $dm = $dm->send(
            $request->input('dm_address'),
            $patient->carePlan->toPdf(),
            now()->toDateTimeString()." - Patient ID $patientId Care Plan.pdf",
            null,
            $patient
        );

        dd($dm);
    }
}

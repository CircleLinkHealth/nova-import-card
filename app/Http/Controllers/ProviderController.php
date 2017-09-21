<?php

namespace App\Http\Controllers;

use App\Events\CarePlanWasApproved;
use App\User;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function approveCarePlan(Request $request, $patientId)
    {
        event(new CarePlanWasApproved(User::find($patientId)));

        return redirect()->to(route('patient.careplan.print', [
            'patientId' => $patientId,
        ]));
    }
}

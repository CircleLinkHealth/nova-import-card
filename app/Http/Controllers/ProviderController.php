<?php

namespace App\Http\Controllers;

use App\Events\CarePlanWasApproved;
use App\User;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function approveCarePlan(Request $request, $patientId, $viewNext = false)
    {
        event(new CarePlanWasApproved(User::find($patientId)));
        $viewNext = (boolean) $viewNext;

        if ($viewNext) {
            $nextPatient = auth()->user()->patientsPendingApproval()->first();

            if ($nextPatient) {
                $patientId = $nextPatient->id;
            }

            return redirect()->to('/');
        }

        return redirect()->to(route('patient.careplan.print', [
            'patientId' => $patientId,
            'clearSession' => $viewNext
        ]));
    }
}

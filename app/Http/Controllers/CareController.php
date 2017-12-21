<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class CareController extends Controller
{
    public function enroll($enrollUserId)
    {
        if (!$enrollUserId) {
            return redirect()->route('home');
        }
        else {
            $patient = User::find($enrollUserId);
            if (!$patient) {
                return redirect()->route('patient.careplan.print', ['patientId' => $enrollUserId]);
            }
            else {
                if (!$patient->isCCMEligible()) {
                    return redirect()->route('patient.careplan.print', ['patientId' => $enrollUserId]);
                }
                else {
                    return view('care.index', [
                        'enrollUserId' => $enrollUserId
                    ]);
                }
            }
        }
    }
}

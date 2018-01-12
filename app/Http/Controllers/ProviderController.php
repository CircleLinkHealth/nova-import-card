<?php

namespace App\Http\Controllers;

use App\Events\CarePlanWasApproved;
use App\User;
use App\Services\ProviderInfoService;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    private $providerInfoService;

    public function __construct(ProviderInfoService $providerInfoService) {
        $this->providerInfoService = $providerInfoService;
    }

    public function index() {
        return $this->providerInfoService->providers();
    }
    
    public function show($id) {
        return $this->providerInfoService->repo()->provider($id);
    }

    public function approveCarePlan(Request $request, $patientId, $viewNext = false)
    {
        event(new CarePlanWasApproved(User::find($patientId)));
        $viewNext = (boolean) $viewNext;

        if ($viewNext) {
            $nextPatient = auth()->user()->patientsPendingApproval()->first();

            if (!$nextPatient) {
                return redirect()->to('/');
            }

            $patientId = $nextPatient->id;
        }

        return redirect()->to(route('patient.careplan.print', [
            'patientId' => $patientId,
            'clearSession' => $viewNext
        ]));
    }
}

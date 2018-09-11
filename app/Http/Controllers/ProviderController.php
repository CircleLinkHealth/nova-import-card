<?php

namespace App\Http\Controllers;

use App\Events\CarePlanWasApproved;
use App\User;
use App\CarePlan;
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

    public function list() {
        return $this->providerInfoService->repo()->list();
    }
    
    public function show($id) {
        return $this->providerInfoService->repo()->provider($id);
    }

    public function approveCarePlan(Request $request, $patientId, $viewNext = false)
    {
        $validator = User::find($patientId)->carePlan()->first()->validateCarePlan();
        if ($validator->fails()){
            return redirect()->to(route('patient.careplan.print', [
                'patientId' => $patientId,
                'clearSession' => $viewNext,
            ]));
        }

        event(new CarePlanWasApproved(User::find($patientId)));
        $viewNext = (boolean) $viewNext;

        if ($viewNext) {
            $nextPatient = auth()->user()->patientsPendingApproval()->get()->filter(function ($user) {
                return $user->careplanStatus == CarePlan::QA_APPROVED;
            })->first();

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

<?php

namespace App\Http\Controllers;

use App\CarePlan;
use App\Events\CarePlanWasApproved;
use App\Services\ProviderInfoService;
use App\User;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    private $providerInfoService;

    public function __construct(ProviderInfoService $providerInfoService)
    {
        $this->providerInfoService = $providerInfoService;
    }

    public function index()
    {
        return $this->providerInfoService->providers();
    }

    public function list()
    {
        return $this->providerInfoService->repo()->list();
    }

    public function show($id)
    {
        return $this->providerInfoService->repo()->provider($id);
    }

    public function approveCarePlan(Request $request, $patientId, $viewNext = false)
    {
        $validator = CarePlan::where('user_id', $patientId)->first()->validateCarePlan();
        if ($validator->fails()) {
            return redirect()->back()->with(['errors' => $validator->errors()]);
        }

        event(new CarePlanWasApproved(User::find($patientId)));
        $viewNext = (boolean)$viewNext;

        if ($viewNext) {
            $nextPatient = auth()->user()->patientsPendingApproval()->get()->filter(function ($user) {
                return $user->careplanStatus == CarePlan::QA_APPROVED;
            })->first();

            if ( ! $nextPatient) {
                return redirect()->to('/');
            }

            $patientId = $nextPatient->id;
        }

        return redirect()->to(route('patient.careplan.print', [
            'patientId'    => $patientId,
            'clearSession' => $viewNext,
        ]));
    }
}

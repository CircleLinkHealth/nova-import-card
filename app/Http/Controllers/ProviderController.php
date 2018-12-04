<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

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

    public function approveCarePlan(Request $request, $patientId, $viewNext = false)
    {
        if (auth()->user()->canQAApproveCarePlans()) {
            $carePlan = CarePlan::where('user_id', $patientId)
                ->firstOrFail();

            if (CarePlan::DRAFT == $carePlan->status && $carePlan->validator()->fails()) {
                return redirect()->back()->with(['errors' => $carePlan->validator()->errors()]);
            }
        }

        event(new CarePlanWasApproved(User::find($patientId)));
        $viewNext = (bool) $viewNext;

        if ($viewNext) {
            $nextPatient = auth()->user()->patientsPendingApproval()->get()->filter(function ($user) {
                return CarePlan::QA_APPROVED == $user->getCarePlanStatus();
            })->first();

            if (!$nextPatient) {
                return redirect()->to('/');
            }

            $patientId = $nextPatient->id;
        }

        return redirect()->to(route('patient.careplan.print', [
            'patientId'    => $patientId,
            'clearSession' => $viewNext,
        ]));
    }

    public function index()
    {
        return $this->providerInfoService->providers();
    }

    public function list()
    {
        return $this->providerInfoService->repo()->list();
    }

    public function removePatient($patientId, $viewNext = false)
    {
        $user = User::find($patientId);

        if (!$user) {
            return response('User not found', 401);
        }

        try {
            $user->delete();
        } catch (\Exception $e) {
            report($e);
        }

        if ($viewNext) {
            $nextPatient = auth()->user()->patientsPendingApproval()->get()->filter(function ($user) {
                return CarePlan::QA_APPROVED == $user->getCarePlanStatus();
            })->first();

            if (!$nextPatient) {
                return redirect()->to('/');
            }

            $patientId = $nextPatient->id;

            return redirect()->to(route('patient.careplan.print', [
                'patientId'    => $patientId,
                'clearSession' => $viewNext,
            ]));
        }

        return redirect()->to('/');
    }

    public function show($id)
    {
        return $this->providerInfoService->repo()->provider($id);
    }
}

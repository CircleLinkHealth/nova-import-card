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
        if (auth()->user()->canQAApproveCarePlans()) {
            $carePlan = CarePlan::where('user_id', $patientId)
                                ->firstOrFail();

            if ($carePlan->status == CarePlan::DRAFT && $carePlan->validator()->fails()) {
                return redirect()->back()->with(['errors' => $carePlan->validator()->errors()]);
            }
        }

        event(new CarePlanWasApproved(User::find($patientId)));
        $viewNext = (boolean)$viewNext;

        if ($viewNext) {
            $nextPatient = auth()->user()->patientsPendingApproval()->get()->filter(function ($user) {
                return $user->getCarePlanStatus() == CarePlan::QA_APPROVED;
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

    public function removePatient($patientId, $viewNext = false)
    {
        $user = User::find($patientId);

        if ( ! $user) {
            return response("User not found", 401);
        }

        try {
            $user->delete();
        } catch (\Exception $e) {
            report($e);
        }

        if ($viewNext) {
            $nextPatient = auth()->user()->patientsPendingApproval()->get()->filter(function ($user) {
                return $user->getCarePlanStatus() == CarePlan::QA_APPROVED;
            })->first();

            if ( ! $nextPatient) {
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
}

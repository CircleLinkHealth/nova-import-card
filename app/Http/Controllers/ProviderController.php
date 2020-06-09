<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Events\CarePlanWasApproved;
use App\Services\ProviderInfoService;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
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

            if (CarePlan::DRAFT == $carePlan->status) {
                $validator = $carePlan->validator($request->has('confirm_diabetes_conditions'));
                if ($validator->fails()) {
                    return redirect()->back()->with(['errors' => $validator->errors()]);
                }
            }
        }

        event(new CarePlanWasApproved(User::find($patientId), auth()->user()));
        $viewNext = (bool) $viewNext;

        if ($viewNext && auth()->user()->hasRole(['administrator', 'provider'])) {
            if (auth()->user()->isProvider()) {
                $nextPatient = User::patientsPendingProviderApproval(auth()->user())->first();
            } elseif (auth()->user()->isAdmin()) {
                $nextPatient = User::patientsPendingCLHApproval(auth()->user())->first();
            }

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

    public function index()
    {
        return $this->providerInfoService->providers();
    }

    public function list()
    {
        return $this->providerInfoService->list();
    }

    public function listLocations()
    {
        return Location::whereIn('practice_id', auth()->user()->viewableProgramIds())->whereNotNull('name')->get()->transform(function ($location) {
            return [
                'id'   => $location->id,
                'name' => $location->name,
            ];
        });
    }

    public function removePatient($patientId, $viewNext = false)
    {
        $user = User::find($patientId);

        if ( ! $user) {
            return response('User not found', 401);
        }

        try {
            $user->delete();
        } catch (\Exception $e) {
            report($e);
        }

        if ($viewNext) {
            $nextPatient = User::patientsPendingProviderApproval(auth()->user())->first();

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

    public function show($id)
    {
        return $this->providerInfoService->provider($id);
    }

    public function updateApproveOwnCarePlan(Request $request)
    {
        $user = auth()->user();
        if ( ! $user->providerInfo) {
            return redirect()->back()->withErrors(['errors' => 'Please log in as a Provider.']);
        }

        $user->providerInfo->approve_own_care_plans = ! $user->providerInfo->approve_own_care_plans;

        $user->providerInfo->save();

        return redirect()->route('patients.dashboard');
    }
}

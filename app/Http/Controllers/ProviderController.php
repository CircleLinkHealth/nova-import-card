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
use Illuminate\Support\MessageBag;

class ProviderController extends Controller
{
    public const SESSION_RN_APPROVED_KEY = 'rn_approved';
    
    private $providerInfoService;

    public function __construct(ProviderInfoService $providerInfoService)
    {
        $this->providerInfoService = $providerInfoService;
    }

    public function approveCarePlan(Request $request, $patientId, $viewNext = false)
    {
        /** @var User $user */
        $user = auth()->user();
        if ($user->canRNApproveCarePlans()) {
            /** @var CarePlan $carePlan */
            $carePlan = CarePlan::where('user_id', $patientId)
                ->firstOrFail();

            if (CarePlan::QA_APPROVED !== $carePlan->status) {
                $bag = new MessageBag(['status' => 'careplan must be qa_approved in order to be rn_approved']);

                return redirect()->back()->with(['errors' => $bag]);
            }

            // this will be used when creating a note
            // the care plan status will be changed only when the note is saved
            $session = $request->session();
            $session->put(ProviderController::SESSION_RN_APPROVED_KEY, auth()->id());
        } else {
            if ($user->canQAApproveCarePlans()) {
                /** @var CarePlan $carePlan */
                $carePlan = CarePlan::where('user_id', $patientId)
                    ->firstOrFail();

                if (CarePlan::DRAFT == $carePlan->status) {
                    $validator = $carePlan->validator($request->has('confirm_diabetes_conditions'));
                    if ($validator->fails()) {
                        return redirect()->back()->with(['errors' => $validator->errors()]);
                    }
                }
            }

            event(new CarePlanWasApproved(User::find($patientId), $user));
            $viewNext = (bool) $viewNext;

            if ($viewNext) {
                $nextPatient = $this->getNextPatient($user);

                if ( ! $nextPatient) {
                    return redirect()->to('/');
                }

                $patientId = $nextPatient->id;
            }
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

    public function removePatient($patientId)
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

        $nextPatient = $this->getNextPatient(auth()->user());

        if ( ! $nextPatient) {
            return redirect()->to('/');
        }

        $patientId = $nextPatient->id;

        if ( ! $patientId) {
            return redirect()->to('/');
        }

        return redirect()->to(route('patient.careplan.print', [
            'patientId'    => $patientId,
            'clearSession' => true,
        ]));
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

    private function getNextPatient(User $user)
    {
        if ($user->canApproveCarePlans()) {
            return User::patientsPendingProviderApproval($user)->first();
        }

        if ($user->canQAApproveCarePlans()) {
            return User::patientsPendingCLHApproval($user)->first();
        }

        return null;
    }
}

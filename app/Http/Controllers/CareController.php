<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Services\UserService;
use CircleLinkHealth\SharedModels\Services\CareplanService;
use Illuminate\Http\Request;

class CareController extends Controller
{
    private $careplanService;
    private $userService;

    public function __construct(UserService $userService, CareplanService $careplanService)
    {
        $this->userService     = $userService;
        $this->careplanService = $careplanService;
    }

    public function enroll($enrollUserId)
    {
        return $this->validate_user_id($enrollUserId, [$this, 'render']);
    }

    public function render($enrollUserId)
    {
        return view('care.index', [
            'enrollUserId' => $enrollUserId,
        ]);
    }

    public function store($enrollUserId, Request $request)
    {
        $status = $request->input('status');

        return $this->validate_user_id($enrollUserId, function () use ($enrollUserId, $status) {
            if ('rejected' == $status) {
                $this->careplanService->repo()->reject($enrollUserId, optional(auth()->user())->id);

                return redirect()->route('patient.careplan.print', ['patientId' => $enrollUserId]);
            }

            return $this->render($enrollUserId);
        });
    }

    public function validate_user_id($enrollUserId, $callbackFn)
    {
        if ( ! $enrollUserId) {
            return redirect('/');
        }
        $patient = User::find($enrollUserId);
        if ( ! $patient) {
            return redirect()->route('patient.careplan.print', ['patientId' => $enrollUserId]);
        }
        if ( ! $patient->isCCMEligible()) {
            return redirect()->route('patient.careplan.print', ['patientId' => $enrollUserId]);
        }

        return call_user_func($callbackFn, $enrollUserId);
    }
}

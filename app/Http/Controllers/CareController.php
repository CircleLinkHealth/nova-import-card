<?php

namespace App\Http\Controllers;

use App\User;
use App\Services\UserService;
use App\Services\CareplanService;
use App\Repositories\CareplanRepository;
use Illuminate\Http\Request;

class CareController extends Controller
{
    private $userService;
    private $careplanService;

    public function __construct(UserService $userService, CareplanService $careplanService)
    {
        $this->userService = $userService;
        $this->careplanService = $careplanService;
    }

    public function validate_user_id($enrollUserId, $callbackFn)
    {
        if (!$enrollUserId) {
            return redirect('/');
        } else {
            $patient = User::find($enrollUserId);
            if (!$patient) {
                return redirect()->route('patient.careplan.print', ['patientId' => $enrollUserId]);
            } else {
                if (!$patient->isCCMEligible()) {
                    return redirect()->route('patient.careplan.print', ['patientId' => $enrollUserId]);
                } else {
                    return call_user_func($callbackFn, $enrollUserId);
                }
            }
        }
    }

    public function render($enrollUserId)
    {
        return view('care.index', [
            'enrollUserId' => $enrollUserId
        ]);
    }

    public function enroll($enrollUserId)
    {
        return $this->validate_user_id($enrollUserId, [$this, 'render']);
    }

    public function store($enrollUserId, Request $request)
    {
        $status = $request->input('status');
        
        return $this->validate_user_id($enrollUserId, function () use ($enrollUserId, $status) {
            if ($status == 'rejected') {
                $this->careplanService->repo()->reject($enrollUserId, optional(auth()->user())->id);
                return redirect()->route('patient.careplan.print', ['patientId' => $enrollUserId]);
            }
            return $this->render($enrollUserId);
        });
    }
}

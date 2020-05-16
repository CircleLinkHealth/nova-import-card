<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment\Auth;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;

trait EnrollmentAuthentication
{
    private function authenticateLink(Request $request)
    {
        if ( ! $request->hasValidSignature()) {
            abort(403, 'Unauthorized action.');
        }

        return true;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|User
     */
    private function getUser(int $userId)
    {
        return User::with(['primaryPractice', 'billingProvider', 'patientInfo'])
            ->where('id', $userId)
            ->first();
    }

    private function getUserId($request)
    {
        return intval($request->input('enrollable_id'));
    }
}

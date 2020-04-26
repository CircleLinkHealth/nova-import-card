<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment\Auth;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;

trait EnrollmentAuthLink
{
//    private function authenticateLink(Request $request)
//    {
//        if ( ! $request->hasValidSignature()) {
//            abort(403, 'Unauthorized action.');
//        }
//    }
//
//    /**
//     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|User
//     */
//    private function getUser(int $userId)
//    {
//        try {
//            // @var User $user
//            return User::with(['primaryPractice', 'billingProvider', 'patientInfo'])
//                ->where('id', $userId)
//                ->firstOrFail();
//        } catch (\Exception $exception) {
//            abort(403, 'Unauthorized action.');
//        }
//    }
//
//    private function getUserId($request)
//    {
//        return intval($request->input('enrollable_id'));
//    }
//
//    private function getUserValidated(Request $request)
//    {
//        $userId = $this->getUserId($request);
//
//        $user = $this->getUser($userId);
//
//        if ($userId !== $user->id) {
//            abort(403, 'Unauthorized action.');
//        }
//
//        return $user;
//    }
}

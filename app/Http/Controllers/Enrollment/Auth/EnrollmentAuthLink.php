<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment\Auth;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;

trait EnrollmentAuthLink
{
    public function authenticate(Request $request)
    {
        if ( ! $request->hasValidSignature()) {
            abort(403, 'Unauthorized action.');
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|User
     */
    private function getUser(int $userId)
    {
        try {
            // @var User $user
            return User::with(['primaryPractice', 'billingProvider'])
                ->where('id', $userId)
                ->firstOrFail();
        } catch (\Exception $exception) {
            abort(403, 'Unauthorized action.');
        }
    }

    private function getUserValidated(Request $request)
    {
        $userId = intval($request->input('enrollable_id'));

        $user = $this->getUser($userId);

        if ($userId !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        return $user;
    }
}

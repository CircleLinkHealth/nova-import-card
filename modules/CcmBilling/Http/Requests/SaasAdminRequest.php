<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Http\Requests;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Foundation\Http\FormRequest;

class SaasAdminRequest extends FormRequest
{
    public function authorize()
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user->isAdmin()) {
            return true;
        }

        $practiceId = $this->input('practice_id');
        if ( ! empty($practiceId)) {
            return $user->hasRoleForSite('software-only', $practiceId);
        }

        return $user->hasRole('software-only');
    }
}

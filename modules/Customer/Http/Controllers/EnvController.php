<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Http\Controllers;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;

class EnvController
{
    public function getEnv(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();
        if ( ! $user->isAdmin()) {
            return response([], 403);
        }

        if (isProductionEnv() && ! in_array($user->email, ['mantoniou@circlelinkhealth.com', 'pangratios@circlelinkhealth.com'])) {
            return response([], 403);
        }

        return response()->json(config()->all());
    }
}

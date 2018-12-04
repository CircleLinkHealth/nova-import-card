<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Patient\Traits;

use Illuminate\Http\Request;

trait ProviderInfoTraits
{
    public function addProvider($userId, Request $request)
    {
        $provider_id = $request->input('provider_id');
        throw new Exception('Not Implemented Yet');
    }

    public function getProviders($userId)
    {
        return $this->json($this->providerService->getPatientProviders($userId));
    }

    public function removeProvider($userId, $provider_id)
    {
        throw new Exception('Not Implemented Yet');
    }
}

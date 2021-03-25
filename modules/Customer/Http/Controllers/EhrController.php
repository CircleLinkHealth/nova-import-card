<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Http\Controllers;

use CircleLinkHealth\Customer\Entities\Ehr;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class EhrController extends Controller
{
    const SSO_INTEGRATIONS = [
        'athena',
    ];

    public function index(Request $request)
    {
        $onlySso = $request->boolean('onlySso', false);
        $ehrs    = Ehr::when(
            $onlySso,
            function ($q) {
                return $q->whereIn('name', self::SSO_INTEGRATIONS);
            }
        )
            ->get()
            ->map(fn (Ehr $item) => ['id' => $item->id, 'name' => $item->name])
            ->toArray();

        return response()->json($ehrs);
    }
}

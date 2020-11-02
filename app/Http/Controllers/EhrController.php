<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use CircleLinkHealth\Customer\Entities\Ehr;
use Illuminate\Http\Request;

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
                return $q->whereIn('name', ['athena']);
            }
        )
            ->get()
            ->map(fn (Ehr $item) => ['id' => $item->id, 'name' => $item->name])
            ->toArray();

        return response()->json($ehrs);
    }
}

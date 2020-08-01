<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use CircleLinkHealth\SharedModels\Entities\CpmSymptom;

class SymptomController extends Controller
{
    /**
     * returns a list of paginated Medication in the system.
     */
    public function index()
    {
        return response()->json(CpmSymptom::paginate());
    }
}

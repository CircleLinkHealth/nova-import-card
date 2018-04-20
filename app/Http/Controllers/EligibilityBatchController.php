<?php

namespace App\Http\Controllers;

use App\EligibilityBatch;
use Illuminate\Http\Request;

class EligibilityBatchController extends Controller
{
    public function show(EligibilityBatch $batch) {
        return view('eligibilityBatch.show', compact(['batch']));
    }
}

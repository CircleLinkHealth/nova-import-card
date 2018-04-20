<?php

namespace App\Http\Controllers;

use App\EligibilityBatch;
use App\Enrollee;
use App\Models\MedicalRecords\Ccda;
use Illuminate\Http\Request;

class EligibilityBatchController extends Controller
{
    public function show(EligibilityBatch $batch) {
        $unprocessed = Ccda::whereBatchId($batch->id)->whereStatus(Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY)->count();
        $ineligible = Ccda::whereBatchId($batch->id)->whereStatus(Ccda::INELIGIBLE)->count();
        $duplicates = Ccda::onlyTrashed()->whereBatchId($batch->id)->whereStatus(Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY)->count();
        $eligible = Enrollee::whereBatchId($batch->id)->count();

        return view('eligibilityBatch.show', compact(['batch', 'unprocessed', 'eligible', 'ineligible', 'duplicates']));
    }
}

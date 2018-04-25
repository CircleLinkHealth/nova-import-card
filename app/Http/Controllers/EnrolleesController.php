<?php

namespace App\Http\Controllers;

use App\EligibilityBatch;
use App\Enrollee;
use App\Models\MedicalRecords\ImportedMedicalRecord;
use App\Practice;
use App\Services\CCD\ProcessEligibilityService;
use Illuminate\Http\Request;

class EnrolleesController extends Controller
{
    public function showBatch(EligibilityBatch $batch)
    {
        $enrollees = Enrollee::whereBatchId($batch->id)->get();
        $practice  = Practice::findOrFail($batch->practice_id);

        return view('admin.enrollees.show-batch', compact(['enrollees', 'practice', 'batch']));
    }

    public function import(Request $request, ProcessEligibilityService $processEligibilityService)
    {
        $enrollee = Enrollee::findOrFail($request->input('enrollee_id'));

        if ($enrollee->user_id) {
            return redirect()->back()
                             ->withInput()
                             ->with([
                                 'message' => 'This patient has already been imported',
                                 'type'    => 'error',
                             ]);
        }

        if ($processEligibilityService->isCcda($enrollee->medical_record_type)) {
            $imr = $processEligibilityService->importExistingCcda($enrollee->medical_record_id);

            if (is_a($imr, ImportedMedicalRecord::class)) {
                $url = link_to_route('import.ccd.remix', 'Click here to Create and a CarePlan and review.');

                return redirect()->back()->with([
                    'message' => "The CCD was imported. $url",
                    'type'    => 'success',
                ]);
            }
        }

        return redirect()->back()
                         ->with([
                             'message' => 'Sorry. Some random error occured. Please post to #qualityassurance to notify everyone to stop using the importer, and also tag Michalis to fix this asap.',
                             'type'    => 'error',
                         ]);
    }

    public function index()
    {
        $enrollees = Enrollee::all();
        $practices = Practice::get()->keyBy('id');

        return view('admin.enrollees.index', compact(['enrollees', 'practices']));
    }
}

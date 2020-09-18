<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Http\Controllers;

use App\Http\Controllers\Controller;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Jobs\ImportConsentedEnrollees;
use CircleLinkHealth\Eligibility\Jobs\ImportMedicalRecordsById;
use Illuminate\Http\Request;

class EnrolleesController extends Controller
{
    public function import(Request $request, EligibilityBatch $batch = null)
    {
        $input = $request->input('enrollee_id');

        if ( ! is_array($input)) {
            $input = [$input];
        }

        ImportConsentedEnrollees::dispatch($input, $batch)->onQueue('low');

        $url = link_to_route('import.ccd.remix', 'Imported CCDAs.');

        return redirect()->back()->with([
            'message' => "A job has been scheduled. Imported CCDs should start showing up in ${url} in 4-5 minutes. Something went wrong otherwise, and you should reach Michalis with a link to the Batch you were trying to import.",
            'type'    => 'success',
        ]);
    }

    public function importArray(Request $request)
    {
        $ids = collect(explode(',', $request->input('enrollee_ids')))->map(function ($id) {
            return trim($id);
        })->filter()->unique()->values();

        $ids->each(function ($id) {
            ImportConsentedEnrollees::dispatch([$id])->onQueue('low');
        });

        $url = link_to_route('import.ccd.remix', 'Imported CCDAs.');

        return [
            'message' => "A job has been scheduled. Imported CCDs should start showing up in ${url} in 5-10 minutes. Importing ".implode(
                ',',
                $ids->all()
            ),
            'type' => 'success',
        ];
    }

    public function importMedicalRecords(Request $request)
    {
        $ids = collect(explode(',', $request->input('medical_record_ids')))->map(function ($id) {
            return trim($id);
        })->filter()->unique()->values()->all();

        $practice = Practice::findOrFail($request->input('practice_id'));

        ImportMedicalRecordsById::dispatch($ids, $practice)->onQueue('low');

        $url = link_to_route('import.ccd.remix', 'Imported CCDAs.');

        return redirect()->back()->with([
            'message' => "A job has been scheduled. Imported CCDs should start showing up in ${url} in 4-5 minutes. Something went wrong otherwise, and you should reach Michalis with a link to the Batch you were trying to import.",
            'type'    => 'success',
        ]);
    }

    public function index()
    {
        $enrollees = Enrollee::orderBy('last_name')->get();
        $practices = Practice::get()->keyBy('id');

        return view('admin.enrollees.index', compact(['enrollees', 'practices']));
    }

    public function showBatch(EligibilityBatch $batch)
    {
        $enrollees = Enrollee::whereBatchId($batch->id)->orderBy('last_name')->get();
        $practice  = Practice::findOrFail($batch->practice_id);

        return view('admin.enrollees.show-batch', compact(['enrollees', 'practice', 'batch']));
    }
}

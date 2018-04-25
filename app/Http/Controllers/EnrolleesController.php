<?php

namespace App\Http\Controllers;

use App\EligibilityBatch;
use App\Enrollee;
use App\Jobs\ImportConsentedEnrollees;
use App\Practice;
use Illuminate\Http\Request;

class EnrolleesController extends Controller
{
    public function showBatch(EligibilityBatch $batch)
    {
        $enrollees = Enrollee::whereBatchId($batch->id)->orderBy('last_name')->get();
        $practice  = Practice::findOrFail($batch->practice_id);

        return view('admin.enrollees.show-batch', compact(['enrollees', 'practice', 'batch']));
    }

    public function import(Request $request, EligibilityBatch $batch)
    {
        $input = $request->input('enrollee_id');

        if ( ! is_array($input)) {
            $input = [$input];
        }

        ImportConsentedEnrollees::dispatch($input, $batch);

        $url = link_to_route('import.ccd.remix', 'Imported CCDAs.');

        return redirect()->back()->with([
            'message' => "A job has been scheduled. Imported CCDs should start showing up in $url in 4-5 minutes. Something went wrong otherwise, and you should reach Michalis with a link to the Batch you were trying to import.",
            'type'    => 'success',
        ]);
    }

    public function importArray(Request $request)
    {
        $ids = collect(explode(',', $request->input('enrollee_ids')))->map(function ($id) {
            return trim($id);
        })->filter()->unique()->values()->all();

        ImportConsentedEnrollees::dispatch($ids);

        $url = link_to_route('import.ccd.remix', 'Imported CCDAs.');

        return redirect()->back()->with([
            'message' => "A job has been scheduled. Imported CCDs should start showing up in $url in 4-5 minutes. Something went wrong otherwise, and you should reach Michalis with a link to the Batch you were trying to import.",
            'type'    => 'success',
        ]);
    }

    public function index()
    {
        $enrollees = Enrollee::orderBy('last_name')->get();
        $practices = Practice::get()->keyBy('id');

        return view('admin.enrollees.index', compact(['enrollees', 'practices']));
    }
}

<?php

namespace App\Http\Controllers;

use App\Enrollee;
use App\Practice;
use App\Services\CCD\ProcessEligibilityService;
use Illuminate\Http\Request;

class EnrolleesController extends Controller
{
    public function index()
    {
        $enrollees = Enrollee::all();
        $practices = Practice::get()->keyBy('id');

        return view('admin.enrollees.index', compact(['enrollees', 'practices']));
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
            $url = link_to_route('import.ccd.remix', 'Click here to Create and a CarePlan and review.');

            $imr = $processEligibilityService->importExistingCcda($enrollee->medical_record_id);

            return redirect()->back()->with([
                'message' => "The CCD was imported. $url",
                'type'    => 'success',
            ]);
        }

        return redirect()->back()
                         ->with([
                             'message' => 'Error',
                             'type'    => 'error',
                         ]);
    }
}

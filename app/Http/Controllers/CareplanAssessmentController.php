<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\CareplanAssessment;
use App\Services\CareplanAssessmentService;
use App\Services\CareplanService;
use CircleLinkHealth\Customer\Services\NoteService;
use Illuminate\Http\Request;

class CareplanAssessmentController extends Controller
{
    private $assessmentService;
    private $careplanService;
    private $noteService;

    public function __construct(CareplanAssessmentService $assessmentService, CareplanService $careplanService, NoteService $noteService)
    {
        $this->assessmentService = $assessmentService;
        $this->careplanService   = $careplanService;
        $this->noteService       = $noteService;
    }

    public function index()
    {
        return response()->json(null);
    }

    public function store(Request $request, CareplanAssessment $assessment)
    {
        $data = $request->all();
        $assessment->process((object) $data);
        $assessment->provider_approver_id = auth()->user()->id;
        if ( ! $assessment->careplan_id) {
            return $this->badRequest('missing parameter "careplan_id"');
        }
        //return response()->json($assessment);
        $this->assessmentService->save($assessment);
        $this->noteService->createAssessmentNote($assessment);

        return redirect()->route('patient.careplan.print', ['patientId' => $assessment->careplan_id, 'recentSubmission' => true]);
    }
}

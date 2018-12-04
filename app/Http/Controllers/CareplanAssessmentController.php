<?php

namespace App\Http\Controllers;

use App\AppConfig;
use App\Http\Controllers\Controller;
use App\CareplanAssessment;
use App\Services\CareplanService;
use App\Services\CareplanAssessmentService;
use App\Services\NoteService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CareplanAssessmentController extends Controller
{
    private $assessmentService;
    private $careplanService;
    private $noteService;

    public function __construct(CareplanAssessmentService $assessmentService, CareplanService $careplanService, NoteService $noteService)
    {
        $this->assessmentService = $assessmentService;
        $this->careplanService = $careplanService;
        $this->noteService = $noteService;
    }

    public function index()
    {
        return response()->json(null);
    }
    
    public function store(Request $request, CareplanAssessment $assessment)
    {
        $data = $request->all();
        $assessment->process((object)$data);
        $assessment->provider_approver_id = auth()->user()->id;
        if (!$assessment->careplan_id) {
            return $this->badRequest('missing parameter "careplan_id"');
        } else {
            //return response()->json($assessment);
            $this->assessmentService->save($assessment);
            $this->noteService->createAssessmentNote($assessment);
            return redirect()->route('patient.careplan.print', [ 'patientId' => $assessment->careplan_id, 'recentSubmission' => true ]);
        }
    }
}

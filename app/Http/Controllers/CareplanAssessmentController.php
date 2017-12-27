<?php

namespace App\Http\Controllers;

use App\AppConfig;
use App\Http\Controllers\Controller;
use App\CareplanAssessment;
use App\Services\CareplanService;
use App\Services\CareplanAssessmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class CareplanAssessmentController extends Controller
{
    private $assessmentService;
    private $careplanService;

    public function __construct(CareplanAssessmentService $assessmentService, CareplanService $careplanService)
    {
        $this->assessmentService = $assessmentService;
        $this->careplanService = $careplanService;
    }

    public function index() {
        return response()->json(null);
    }
    
    public function store(Request $request, CareplanAssessment $assessment) {
        $data = $request->all();
        $assessment->load((object)$data);
        $assessment->provider_approver_id = auth()->user()->id;
        if (!$assessment->careplan_id) {
            return $this->badRequest('missing parameter "careplan_id"');
        }
        else {
            $this->assessmentService->save($assessment);
            return redirect()->route('patient.careplan.print', [ 'patientId' => $assessment->careplan_id ]);
        }
    }
}

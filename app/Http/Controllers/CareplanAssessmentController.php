<?php

namespace App\Http\Controllers;

use App\AppConfig;
use App\Http\Controllers\Controller;
use App\CareplanAssessment;
use App\Services\CareplanAssessmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class CareplanAssessmentController extends Controller
{
    private $assessmentService;

    public function __construct(CareplanAssessmentService $assessmentService)
    {
        $this->assessmentService = $assessmentService;
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
            $assessment->save();
            return response()->json($assessment);
        }
    }
}

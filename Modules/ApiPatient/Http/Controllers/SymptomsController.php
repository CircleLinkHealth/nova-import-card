<?php

namespace CircleLinkHealth\ApiPatient\Http\Controllers;

use App\Services\CPM\CpmSymptomService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class SymptomsController extends Controller
{
    /**
     * @var CpmSymptomService
     */
    protected $symptomService;
    
    /**
     * SymptomsController constructor.
     *
     * @param CpmSymptomService $symptomService
     */
    public function __construct(CpmSymptomService $symptomService)
   {
       $this->symptomService = $symptomService;
   }
    
    public function store($userId, Request $request)
    {
        $symptomId = $request->input('symptomId');
        if ($userId && $symptomId) {
            return $this->symptomService->repo()->addSymptomToPatient($symptomId, $userId);
        }
        
        return \response('"symptomId" and "userId" are important');
    }
    
    public function show($userId)
    {
        if ($userId) {
            return $this->symptomService->repo()->patientSymptoms($userId);
        }
        
        return \response('"userId" is important');
    }
    
    public function destroy($userId, $symptomId)
    {
        if ($userId && $symptomId) {
            $result = $this->symptomService->repo()->removeSymptomFromPatient($symptomId, $userId);
            
            return $result ? response()->json($result) : \response('provided patient does not have the symptom in question');
        }
        
        return \response('"symptomId" and "userId" are important');
    }
}

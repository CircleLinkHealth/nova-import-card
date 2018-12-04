<?php

namespace App\Http\Controllers\Patient\Traits;

use Illuminate\Http\Request;

trait SymptomTraits
{
    public function getSymptoms($userId)
    {
        if ($userId) {
            return $this->symptomService->repo()->patientSymptoms($userId);
        }
        return $this->badRequest('"userId" is important');
    }

    public function addSymptom($userId, Request $request)
    {
        $symptomId = $request->input('symptomId');
        if ($userId && $symptomId) {
            return $this->symptomService->repo()->addSymptomToPatient($symptomId, $userId);
        } else {
            return $this->badRequest('"symptomId" and "userId" are important');
        }
    }
    
    public function removeSymptom($userId, $symptomId)
    {
        if ($userId && $symptomId) {
            $result = $this->symptomService->repo()->removeSymptomFromPatient($symptomId, $userId);
            return $result ? response()->json($result) : $this->notFound('provided patient does not have the symptom in question');
        } else {
            return $this->badRequest('"symptomId" and "userId" are important');
        }
    }
}

<?php

namespace App\Http\Controllers\Patient\Traits;

use Illuminate\Http\Request;

trait AllergyTraits
{
    public function getCcdAllergies($userId)
    {
        return response()->json($this->patientService->getCcdAllergies($userId));
    }
    
    public function addCcdAllergies($userId, Request $request)
    {
        $name = $request->input('name');
        if ($name) {
            return response()->json($this->allergyService->addPatientAllergy($userId, $name));
        } else {
            return $this->badRequest('"name" is important');
        }
    }
    
    public function deleteCcdAllergy($userId, $allergyId)
    {
        if ($userId && $allergyId) {
            return response()->json($this->allergyService->deletePatientAllergy($userId, $allergyId));
        } else {
            return $this->badRequest('"userId" and "allergyId" are important');
        }
    }
}

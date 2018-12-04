<?php

namespace App\Http\Controllers\Patient\Traits;

use Illuminate\Http\Request;

trait LifestyleTraits
{
    public function getLifestyles($userId)
    {
        if ($userId) {
            return $this->lifestyleService->patientLifestyles($userId);
        } else {
            return $this->badRequest('"userId" is important');
        }
    }

    public function addLifestyle($userId, Request $request)
    {
        $lifestyleId = $request->input('lifestyleId');
        if ($userId && $lifestyleId) {
            return $this->lifestyleService->addLifestyleToPatient($lifestyleId, $userId);
        } else {
            return $this->badRequest('"lifestyleId" and "userId" are important');
        }
    }
    
    public function removeLifestyle($userId, $lifestyleId)
    {
        if ($userId && $lifestyleId) {
            return $this->lifestyleService->removeLifestyleFromPatient($lifestyleId, $userId);
        } else {
            return $this->badRequest('"lifestyleId" and "userId" are important');
        }
    }
}

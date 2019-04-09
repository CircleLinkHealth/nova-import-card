<?php

namespace App\Http\Controllers;

use App\Services\SurveyService;
use App\User;
use Illuminate\Http\Request;

class PersonalizedPreventionPlan extends Controller
{
    private $service;

    public function __construct(SurveyService $service)
    {
        $this->service = $service;
    }

    public function getPppDataForUser(Request $request)
    {
        $patientId = '1';

    }
}

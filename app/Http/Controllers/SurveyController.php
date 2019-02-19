<?php

namespace App\Http\Controllers;

use App\Services\SurveyService;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    private $service;

    public function __construct(SurveyService $service)
    {
        $this->service = $service;
    }

    public function getSurvey(){

    }

    public function storeAnswer(Request $request){

        $answer = $this->service->storeAnswer($request->input());

        if (! $answer){
            return response()->json(['errors' => 'Answer was not created'], 400);
        }

        return response()->json(['created' => true], 200);

    }
}

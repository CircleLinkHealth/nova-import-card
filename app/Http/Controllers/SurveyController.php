<?php

namespace App\Http\Controllers;

use App\Answer;
use App\InvitationLink;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function saveAnswer(Request $request)
    {
        //@todo: validation
        $urlToken       = $request->input('link_token');
        $invitationLink = InvitationLink::with('patientInfo')
                                        ->where('link_token', $urlToken)
                                        ->firstOrFail();

        Answer::create([
            'user_id'            => $invitationLink->patientInfo->user_id,
            'survey_id'          => $invitationLink->survey_id,
            'survey_instance_id' => $request->input('survey_instance_id'),
            'question_id'        => $request->input('question_id'),
            'question_answer_id' => $request->input('question_answer_id'),
            'value'              => $request->input('value'),
        ]);
    }
}

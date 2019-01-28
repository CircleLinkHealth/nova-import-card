<?php

namespace App\Http\Controllers;


use App\InvitationLink;
use Carbon\Carbon;

class InvitationLinksController extends Controller
{
    public function sendSms()
    {
        request()->validate([
            'patient_user_id' => 'required',
            'patient_name' => 'required',
            'birth_date' => 'required',
            'survey_id' => 'required',
            'link_token' => 'required',
            'is_expired' => 'required',
        ]);

        $link_token = $this->tokenCreate();
        //save invitation
        InvitationLink::create(request([
            'patient_user_id',
            'patient_name',
            'birth_date',
            'survey_id',
            'link_token',
            'is_expired',
        ]));

        //send sms here
    }

    /**
     * @return string
     */
    protected function tokenCreate(): string
    {
        do {//generate a random string
            $token      = str_random();
            $link_token = 'awv.survey/' . $token;
            //check if the token already exists and if it does, try again
        } while (InvitationLink::where('link_token', $link_token)->exists());

        return $link_token;
    }

    public function checkUserBeforeRedirectToAwv($link_token)
    {
        $date          = today();
        $link          = InvitationLink::where('link_token', $link_token)->first();
        $linkCreatedAt = $link->select('created_at');
        //check if link exists
        if ( ! $link) {
            return 'Not authorized';
        }
        //check if link has expired
        if (Carbon::parse($linkCreatedAt)->diffInDays($date) > 10) {
            InvitationLink::where('link_token', $link_token)->update([
                'is_expired' => true,
            ]);

            return 'Link has expired please click here to receive a new link';
        }

        return redirect(route('')); //to survey
    }
}

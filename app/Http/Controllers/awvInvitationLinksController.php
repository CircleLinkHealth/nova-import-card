<?php

namespace App\Http\Controllers;

use App\AwvInvitationLinks;
use Carbon\Carbon;

class awvInvitationLinksController extends Controller
{
    public function sendSms($phone)
    {
        $link_token = $this->tokenCreate();
        //save invitation
        $invitation = AwvInvitationLinks::create([
            'patient_user_id' => '',
            'patient_name'    => '',
            'birth_date'      => '',
            'survey_id'       => '',
            'link_token'      => $link_token,
            //im setting 'is_expired' boolean = false in the table.
            // Do i have to do anything else here? Or is it better if i set it to false here?
            'is_expired'      => '',
        ]);

        //todo:send sms here...follows dummy query:
        //Sms::to($phone)->send(new InvitationText($invitation));
        return $invitation;
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
        } while (AwvInvitationLinks::where('link_token', $link_token)->exists());

        return $link_token;
    }

    public function checkUserBeforeRedirectToAwv($link_token)
    {
        $date          = today();
        $link          = AwvInvitationLinks::where('link_token', $link_token)->first();
        $linkCreatedAt = $link->select('created_at');
        //check if link exists
        if ( ! $link) {
            return 'Not authorized';
        }
        //check if link has expired
        if (Carbon::parse($linkCreatedAt)->diffInDays($date) > 10) {
            AwvInvitationLinks::where('link_token', $link_token)->update([
                'is_expired' => true,
            ]);

            return 'Link has expired please click here to receive a new link';
        }

        //we could just have one column (token) in the table and check on that??
        // then redirect users to awv here? or trim the link from token?
        return redirect(route('')); //to survey

    }
}

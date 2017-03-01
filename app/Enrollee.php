<?php

namespace App;

use Aloha\Twilio\Twilio;
use Illuminate\Database\Eloquent\Model;

class Enrollee extends Model
{
    /**
     * status = eligible
     */
    const ELIGIBLE = 'eligible';

    /**
     * STATUS TYPES:
     *
     * eligible: just imported to enrollees table, queue of sms recipients.
     * sms_sent: initial sms sent
     * sms_received: patient opened link
     * consented: client consented
     * ccd_obtained: medical records were imported
     * ccd_qaed: QAed, good to go for enrollment
     *
     */

    protected $table = 'enrollees';

    protected $fillable = [
        'id',
        'user_id',
        'provider_id',
        'practice_id',
        // patient_id in EHR Software
        'mrn_number',
        'dob',
        'invite_sent_at',
        'first_name',
        'last_name',
        'address',
        'address_2',
        'city',
        'state',
        'zip',
        'invite_code',
        //primary_phone
        'phone',
        'consented_at',
        'last_attempt_at',
        'attempt_count',
        'preferred_window',
        'preferred_days',
        'status',

        'primary_insurance',
        'secondary_insurance',
        'cell_phone',
        'home_phone',
        'other_phone',
        'email',
        'last_encounter',
        'referring_provider_name',
        'problems',
        'ccm_condition_1',
        'ccm_condition_2',
    ];

    public function user()
    {

        return $this->belongsTo(User::class, 'user_id');

    }

    public function provider()
    {

        return $this->belongsTo(User::class, 'provider_id');

    }

    public function practice()
    {

        return $this->belongsTo(Practice::class, 'practice_id');

    }

    public function sendEnrollmentConsentSMS()
    {

        $twilio = new Twilio(env('TWILIO_SID'), env('TWILIO_TOKEN'), env('TWILIO_FROM'));

        $link = url("join/$this->invite_code");
        $provider_name = User::find($this->provider_id)->fullName;

        $twilio->message($this->getPhone(),
            "Dr. $provider_name has invited you to their new wellness program! Please enroll here: $link");


    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function sendEnrollmentConsentReminderSMS()
    {

        $emjo = 'u"\U0001F31F"';

        $twilio = new Twilio(env('TWILIO_SID'), env('TWILIO_TOKEN'), env('TWILIO_FROM'));

        $link = url("join/$this->invite_code");

        $provider_name = User::find($this->provider_id)->fullName;

        $twilio->message($this->getPhone(),
            "Dr. $provider_name hasn’t heard from you regarding their new wellness program $emjo. Please enroll here: $link");


    }

}

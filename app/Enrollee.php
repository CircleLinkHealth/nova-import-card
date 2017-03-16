<?php

namespace App;

use Aloha\Twilio\Twilio;
use App\CLH\Helpers\StringManipulation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Enrollee extends Model
{
    /**
     * status = eligible
     */
    const ELIGIBLE = 'eligible';

    /**
     * status = to_call
     */
    const TO_CALL = 'call_queue';

    /**
     * status = to_sms
     */
    const TO_SMS = 'sms_queue';

    /**
     * STATUS TYPES:
     * eligble, , , mailed, consented, rejected
     * eligible: just imported to enrollees table, queue of sms recipients.
     * smsed: initial sms sent
     * call_queue: patient will show up in enrollment UI
     * consented: client consented
     * ccd_obtained: medical records were imported
     * ccd_qaed: QAed, good to go for enrollment
     */

    protected $table = 'enrollees';

    protected $fillable = [
        'id',

        'user_id',
        'provider_id',
        'practice_id',
        'care_ambassador_id',

        // patient_id in EHR Software
        'mrn',
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

        'lang',

        'primary_phone',
        'cell_phone',
        'home_phone',
        'other_phone',

        'consented_at',
        'last_attempt_at',
        'attempt_count',
        'preferred_window',
        'preferred_days',
        'status',
        'last_call_outcome_reason',
        'last_call_outcome',
        'primary_insurance',
        'secondary_insurance',
        'email',
        'last_encounter',
        'referring_provider_name',
        'problems',
        'cpm_problem_1',
        'cpm_problem_2',
    ];

    public function user()
    {

        return $this->belongsTo(User::class, 'user_id');

    }

    public function provider()
    {

        return $this->belongsTo(User::class, 'provider_id');

    }

    public function careAmbassador()
    {

        return $this->belongsTo(User::class, 'care_ambassador_id');

    }

    public function practice()
    {

        return $this->belongsTo(Practice::class, 'practice_id');

    }

    public function getProviderFullNameAttribute()
    {

        return $this->provider->fullName ?? null;

    }

    public function getPracticeNameAttribute()
    {

        return $this->practice->name ?? null;

    }

    public function sendEnrollmentConsentSMS()
    {
        $twilio = new Twilio(env('TWILIO_SID'), env('TWILIO_TOKEN'), env('TWILIO_FROM'));

        $link = url("join/$this->invite_code");
        $provider_name = User::find($this->provider_id)->fullName;

        $twilio->message($this->primary_phone,
            "Dr. $provider_name has invited you to their new wellness program! Please enroll here: $link");
    }

    /**
     * Set DOB
     *
     * @param $dob
     */
    public function setDobAttribute($dob)
    {
        $this->attributes['dob'] = Carbon::parse($dob);
    }

    /**
     * Set Home Phone
     *
     * @param $homePhone
     */
    public function setHomePhoneAttribute($homePhone)
    {
        $helper = new StringManipulation();

        $this->attributes['home_phone'] = $helper->formatPhoneNumberE164($homePhone);
    }

    /**
     * Set Cell Phone
     *
     * @param $homePhone
     */
    public function setCellPhoneAttribute($homePhone)
    {
        $helper = new StringManipulation();

        $this->attributes['cell_phone'] = $helper->formatPhoneNumberE164($homePhone);
    }

    /**
     * Set Other Phone
     *
     * @param $homePhone
     */
    public function setOtherPhoneAttribute($homePhone)
    {
        $helper = new StringManipulation();

        $this->attributes['other_phone'] = $helper->formatPhoneNumberE164($homePhone);
    }

    /**
     * Set Primary Phone
     *
     * @param $primaryPhone
     */
    public function setPrimaryPhoneNumberAttribute($primaryPhone)
    {
        $helper = new StringManipulation();

        $this->attributes['primary_phone'] = $helper->formatPhoneNumberE164($primaryPhone);
    }

    /**
     * Get Primary Phone
     *
     * @return mixed
     */
    public function getPrimaryPhoneAttribute($value)
    {
        return $value;
    }

    public function sendEnrollmentConsentReminderSMS()
    {

        $emjo = 'u"\U0001F31F"';

        $twilio = new Twilio(env('TWILIO_SID'), env('TWILIO_TOKEN'), env('TWILIO_FROM'));

        $link = url("join/$this->invite_code");

        $provider_name = User::find($this->provider_id)->fullName;

        $twilio->message($this->primary_phone,
            "Dr. $provider_name hasn’t heard from you regarding their new wellness program $emjo. Please enroll here: $link");


    }

    public function scopeToSMS($query)
    {

        return $query
            ->where('status', self::TO_SMS)
            ->whereNotNull('cell_phone');

    }

    public function scopeToCall($query)
    {
        //@todo add check for where phones are not all null

        return $query->where('status', self::TO_CALL);

    }

}

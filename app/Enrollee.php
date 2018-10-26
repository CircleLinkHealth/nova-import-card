<?php

namespace App;

use App\Facades\StringManipulation;
use App\Models\MedicalRecords\ImportedMedicalRecord;
use Carbon\Carbon;

/**
 * App\Enrollee
 *
 * @property int $id
 * @property string|null $medical_record_type
 * @property int|null $medical_record_id
 * @property int|null $user_id
 * @property int|null $provider_id
 * @property int|null $practice_id
 * @property int|null $care_ambassador_id
 * @property int $total_time_spent
 * @property string|null $last_call_outcome
 * @property string|null $last_call_outcome_reason
 * @property string $mrn
 * @property string $first_name
 * @property string $last_name
 * @property string $address
 * @property string $address_2
 * @property string $city
 * @property string $state
 * @property string $zip
 * @property mixed $primary_phone
 * @property string $other_phone
 * @property string $home_phone
 * @property string $cell_phone
 * @property \Carbon\Carbon|null $dob
 * @property string $lang
 * @property string $invite_code
 * @property string $status
 * @property int $attempt_count
 * @property string|null $preferred_days
 * @property string|null $preferred_window
 * @property string|null $invite_sent_at
 * @property string|null $consented_at
 * @property string|null $last_attempt_at
 * @property string|null $invite_opened_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string $primary_insurance
 * @property string $secondary_insurance
 * @property string $tertiary_insurance
 * @property int|null $has_copay
 * @property string $email
 * @property string $last_encounter
 * @property string $referring_provider_name
 * @property int|null $confident_provider_guess
 * @property string $problems
 * @property int $cpm_problem_1
 * @property int $cpm_problem_2
 * @property string|null $color
 * @property-read \App\CareAmbassador|null $careAmbassador
 * @property-read mixed $practice_name
 * @property-read mixed $provider_full_name
 * @property-read \App\Practice|null $practice
 * @property-read \App\User|null $provider
 * @property-write mixed $primary_phone_number
 * @property-read \App\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee toCall()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee toSMS()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereAttemptCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereCareAmbassadorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereCellPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereConfidentProviderGuess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereConsentedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereCpmProblem1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereCpmProblem2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereDob($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereHasCopay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereHomePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereInviteCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereInviteOpenedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereInviteSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereLang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereLastAttemptAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereLastCallOutcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereLastCallOutcomeReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereLastEncounter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereMedicalRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereMedicalRecordType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereMrn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereOtherPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee wherePracticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee wherePreferredDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee wherePreferredWindow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee wherePrimaryInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee wherePrimaryPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereProblems($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereReferringProviderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereSecondaryInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereTertiaryInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereTotalTimeSpent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereZip($value)
 * @mixin \Eloquent
 */
class Enrollee extends \App\BaseModel
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
        'batch_id',
        'eligibility_job_id',

        'medical_record_type',
        'medical_record_id',

        'user_id',
        'provider_id',
        'practice_id',
        'care_ambassador_id',
        'total_time_spent',

        'invite_sent_at',
        'invite_code',

        'mrn', // patient_id in EHR Software
        'dob',

        'first_name',
        'last_name',
        'address',
        'address_2',
        'city',
        'state',
        'zip',

        'lang', // 'EN' (default) or 'ES'

        'primary_phone',
        'cell_phone',
        'home_phone',
        'other_phone',

        'consented_at',
        'last_attempt_at',
        'attempt_count',
        'status',
        'last_call_outcome_reason',
        'last_call_outcome',

        'preferred_window',
        'preferred_days',

        'primary_insurance',
        'secondary_insurance',
        'tertiary_insurance',
        'has_copay',

        'email',
        'last_encounter',
        'referring_provider_name',
        'problems',
        'cpm_problem_1',
        'cpm_problem_2',
    ];

    protected $dates = [
        'consented_at',
        'dob',
        'invite_opened_at',
        'invite_sent_at',
        'last_attempt_at',
        'last_encounter',
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

        return $this->belongsTo(CareAmbassador::class, 'care_ambassador_id');
    }

    public function practice()
    {

        return $this->belongsTo(Practice::class, 'practice_id');
    }

    public function name()
    {
        return "$this->first_name $this->last_name";
    }

    public function nameAndDob()
    {
        return $this->name() . ', ' . $this->dob->toDateString();
    }

    public function getProviderFullNameAttribute()
    {

        return $this->provider->getFullName() ?? null;
    }

    public function getPracticeNameAttribute()
    {

        return $this->practice->display_name ?? null;
    }

    public function sendEnrollmentConsentSMS()
    {
        $twilio = new Twilio(config('services.twilio.sid'), config('services.twilio.token'),
            config('services.twilio.from'));

        $link          = url("join/$this->invite_code");
        $provider_name = User::find($this->provider_id)->getFullName();

        $twilio->message(
            $this->primary_phone,
            "Dr. $provider_name has invited you to their new wellness program! Please enroll here: $link"
        );
    }

    /**
     * Set DOB
     *
     * @param $dob
     */
    public function setDobAttribute($dob)
    {
        $this->attributes['dob'] = is_a($dob, Carbon::class) ? $dob : Carbon::parse($dob);
    }

    /**
     * Set Home Phone
     *
     * @param $homePhone
     */
    public function setHomePhoneAttribute($homePhone)
    {
        $this->attributes['home_phone'] = StringManipulation::formatPhoneNumberE164($homePhone);
    }

    /**
     * Set Cell Phone
     *
     * @param $homePhone
     */
    public function setCellPhoneAttribute($homePhone)
    {
        $this->attributes['cell_phone'] = StringManipulation::formatPhoneNumberE164($homePhone);
    }

    /**
     * Set Other Phone
     *
     * @param $homePhone
     */
    public function setOtherPhoneAttribute($homePhone)
    {
        $this->attributes['other_phone'] = StringManipulation::formatPhoneNumberE164($homePhone);
    }

    /**
     * Set Primary Phone
     *
     * @param $primaryPhone
     */
    public function setPrimaryPhoneNumberAttribute($primaryPhone)
    {
        $this->attributes['primary_phone'] = StringManipulation::formatPhoneNumber($primaryPhone);
    }

    /**
     * Get Primary Phone in E164 format
     *
     * @return mixed
     */
    public function getPrimaryPhoneE164Attribute()
    {
        return StringManipulation::formatPhoneNumberE164($this->primary_phone);
    }

    public function sendEnrollmentConsentReminderSMS()
    {

        $emjo = 'u"\U0001F31F"';

        $twilio = new Twilio(config('services.twilio.sid'), config('services.twilio.token'),
            config('services.twilio.from'));

        $link = url("join/$this->invite_code");

        $provider_name = User::find($this->provider_id)->getFullName();

        $twilio->message(
            $this->primary_phone,
            "Dr. $provider_name hasn’t heard from you regarding their new wellness program $emjo. Please enroll here: $link"
        );
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

    /**
     * Get Home Phone in E164 format
     *
     * @return
     */
    public function getHomePhoneE164Attribute()
    {
        return StringManipulation::formatPhoneNumberE164($this->home_phone);
    }

    /**
     * Get Cell Phone in E164 format
     *
     * @return
     */
    public function getCellPhoneE164Attribute()
    {
        return StringManipulation::formatPhoneNumberE164($this->cell_phone);
    }

    /**
     * Get Other Phone in E164 format
     *
     * @return
     */
    public function getOtherPhoneE164Attribute()
    {
        return StringManipulation::formatPhoneNumberE164($this->other_phone);
    }

    /**
     * Get Home Phone
     *
     * @param $homePhone
     *
     * @return
     */
    public function getHomePhoneAttribute($homePhone)
    {
        return StringManipulation::formatPhoneNumber($homePhone);
    }

    /**
     * Get Cell Phone
     *
     * @param $cellPhone
     *
     * @return
     */
    public function getCellPhoneAttribute($cellPhone)
    {
        return StringManipulation::formatPhoneNumber($cellPhone);
    }

    /**
     * Get Other Phone
     *
     * @param $otherPhone
     *
     * @return
     */
    public function getOtherPhoneAttribute($otherPhone)
    {
        return StringManipulation::formatPhoneNumber($otherPhone);
    }

    /**
     * Get Other Phone
     *
     * @param $primaryPhone
     *
     * @return
     */
    public function getPrimaryPhoneAttribute($primaryPhone)
    {
        return StringManipulation::formatPhoneNumber($primaryPhone);
    }

    public function getImportedMedicalRecord()
    {
        return ImportedMedicalRecord::whereMedicalRecordId($this->medical_record_id)
                                    ->whereMedicalRecordType($this->medical_record_id)
                                    ->first();
    }

    public function targetPatient()
    {
        return $this->hasOne(TargetPatient::class);
    }

    public function eligibilityJob()
    {
        return $this->belongsTo(EligibilityJob::class);
    }
}

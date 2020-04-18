<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Entities;

use App\Contracts\Services\TwilioClientable;
use App\Traits\HasEnrollableInvitation;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Core\Filters\Filterable;
use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Core\Traits\MySQLSearchable;
use CircleLinkHealth\Core\Traits\Notifiable;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Ccda;

/**
 * CircleLinkHealth\Eligibility\Entities\Enrollee
 *
 * @property int $id
 * @property int|null $batch_id
 * @property int|null $eligibility_job_id
 * @property string|null $medical_record_type
 * @property int|null $medical_record_id
 * @property int|null $user_id
 * @property int|null $provider_id
 * @property int|null $practice_id
 * @property int|null $care_ambassador_user_id
 * @property int $total_time_spent
 * @property string|null $last_call_outcome
 * @property string|null $last_call_outcome_reason
 * @property string|null $mrn
 * @property string $first_name
 * @property string $last_name
 * @property string|null $address
 * @property string|null $address_2
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip
 * @property $primary_phone
 * @property $other_phone
 * @property $home_phone
 * @property $cell_phone
 * @property \Illuminate\Support\Carbon|null $dob
 * @property string|null $lang
 * @property string|null $invite_code
 * @property string|null $status
 * @property int|null $attempt_count
 * @property string|null $preferred_days
 * @property string|null $preferred_window
 * @property \Illuminate\Support\Carbon|null $invite_sent_at
 * @property \Illuminate\Support\Carbon|null $consented_at
 * @property \Illuminate\Support\Carbon|null $last_attempt_at
 * @property \Illuminate\Support\Carbon|null $invite_opened_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $primary_insurance
 * @property string|null $secondary_insurance
 * @property string|null $tertiary_insurance
 * @property int|null $has_copay
 * @property string|null $email
 * @property \Illuminate\Support\Carbon|null $last_encounter
 * @property string|null $referring_provider_name
 * @property int|null $confident_provider_guess
 * @property string|null $problems
 * @property int|null $cpm_problem_1
 * @property int|null $cpm_problem_2
 * @property string|null $color
 * @property \Illuminate\Support\Carbon|null $requested_callback
 * @property array|null $agent_details
 * @property int|null $enrollment_non_responsive
 * @property-read \CircleLinkHealth\Customer\Entities\User|null $careAmbassador
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Eligibility\Entities\Enrollee[] $confirmedFamilyMembers
 * @property-read int|null $confirmed_family_members_count
 * @property-read \CircleLinkHealth\Eligibility\Entities\EligibilityJob|null $eligibilityJob
 * @property-read \App\EnrollableInvitationLink $enrollmentInvitationLink
 * @property-read mixed $agent
 * @property-read $cell_phone_e164
 * @property-read $home_phone_e164
 * @property-read $other_phone_e164
 * @property-read mixed $practice_name
 * @property-read mixed $primary_phone_e164
 * @property-read mixed $provider_full_name
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\CircleLinkHealth\Core\Entities\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \CircleLinkHealth\Customer\Entities\Practice|null $practice
 * @property-read \CircleLinkHealth\Customer\Entities\User|null $provider
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Revisionable\Entities\Revision[] $revisionHistory
 * @property-read int|null $revision_history_count
 * @property-write mixed $primary_phone_number
 * @property-read \App\EnrollableRequestInfo $statusRequestsInfo
 * @property-read \CircleLinkHealth\Eligibility\Entities\TargetPatient $targetPatient
 * @property-read \CircleLinkHealth\Customer\Entities\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee filter(\App\Filters\QueryFilters $filters)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee mySQLSearch($columns, $term, $mode = 'BOOLEAN', $shouldRequireAll = true, $shouldRequireIntegers = true)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee query()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee searchAddresses($term)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee searchPhones($term)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee shouldSuggestAsFamilyForEnrollee($enrolleeId)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee toCall()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee toSMS()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereAgentDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereAttemptCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereCareAmbassadorUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereCellPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereConfidentProviderGuess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereConsentedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereCpmProblem1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereCpmProblem2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereDob($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereEligibilityJobId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereEnrollmentNonResponsive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereHasCopay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereHomePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereInviteCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereInviteOpenedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereInviteSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereLang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereLastAttemptAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereLastCallOutcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereLastCallOutcomeReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereLastEncounter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereMedicalRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereMedicalRecordType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereMrn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereOtherPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee wherePracticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee wherePreferredDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee wherePreferredWindow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee wherePrimaryInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee wherePrimaryPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereProblems($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereReferringProviderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereRequestedCallback($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereSecondaryInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereTertiaryInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereTotalTimeSpent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereZip($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee duplicates(\CircleLinkHealth\Customer\Entities\User $patient, \CircleLinkHealth\SharedModels\Entities\Ccda $ccda)
 * @property int|null $location_id
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereLocationId($value)
 */
class Enrollee extends BaseModel
{
    use Filterable;
    use HasEnrollableInvitation;
    use MySQLSearchable;
    use Notifiable;

    // Agent array keys
    const AGENT_EMAIL_KEY = 'email';
    const AGENT_NAME_KEY = 'name';
    const AGENT_PHONE_KEY = 'phone';
    const AGENT_RELATIONSHIP_KEY = 'relationship';

    /**
     * status = consented.
     */
    const CONSENTED = 'consented';

    /**
     * status = eligible.
     */
    const ELIGIBLE = 'eligible';

    /**
     * status = engaged. When a care ambassador has viewed an enrollee but hasn't actually performed any action on them.
     */
    const ENGAGED = 'engaged';

    /**
     * status = enrolled.
     */
    const ENROLLED = 'enrolled';

    /**
     * status = ineligible.
     */
    const INELIGIBLE = 'ineligible';

    /**
     * status = legacy. These are enrolees who have existed in our system before releasing the care ambassador channel.
     */
    const LEGACY = 'legacy';

    /**
     * status = rejected.
     *
     * (a.k.a hard rejected/declined)
     */
    const REJECTED = 'rejected';

    /**
     * status = rejected.
     */
    const SOFT_REJECTED = 'soft_rejected';

    /**
     * status = to_call.
     */
    const TO_CALL = 'call_queue';

    /**
     * status = to_sms.
     */
    const TO_SMS = 'sms_queue';

    /**
     * status = utc.
     */
    const UNREACHABLE = 'utc';

    /**
     * Enrollees who did not respond to any of our notifications to enroll.
     */

    const NON_RESPONSIVE = 'non_responsive';

    /**
     * For mySql full-text search
     *
     * @var array
     */
    public $phoneAttributes = [
        'cell_phone',
        'home_phone',
        'other_phone',
    ];

    /**
     *
     * For mySql full-text search
     *
     * @var array
     */
    public $addressAttributes = [
        'address',
        'address_2',
    ];

    public $phi = [
        'first_name',
        'last_name',
        'address',
        'address_2',
        'city',
        'state',
        'zip',
        'primary_phone',
        'cell_phone',
        'home_phone',
        'other_phone',
        'primary_insurance',
        'secondary_insurance',
        'tertiary_insurance',
        'has_copay',
        'email',
        'agent_details',
    ];

    protected $casts = [
        'agent_details' => 'array',
    ];

    protected $dates = [
        'consented_at',
        'dob',
        'invite_opened_at',
        'invite_sent_at',
        'last_attempt_at',
        'last_encounter',
        'requested_callback',
    ];

    protected $fillable = [
        'id',
        'batch_id',
        'eligibility_job_id',

        'medical_record_type',
        'medical_record_id',

        'user_id',
        'provider_id',
        'practice_id',
        'care_ambassador_user_id',
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

        'requested_callback',

        //contains array of agent details, similar to patient_info fields
        'agent_details',
//
        'enrollment_non_responsive',
        'auto_enrollment_triggered'
    ];

    protected $table = 'enrollees';

    public function careAmbassador()
    {
        return $this->belongsTo(User::class, 'care_ambassador_user_id');
    }

    public function eligibilityJob()
    {
        return $this->belongsTo(EligibilityJob::class);
    }

    /**
     * @param mixed $key
     */
    public function getAgentAttribute($key)
    {
        if (empty($this->agent_details)) {
            return null;
        }

        if (!array_key_exists($key, $this->agent_details)) {
            return null;
        }

        return $this->agent_details[$key];
    }

    /**
     * Get Cell Phone.
     *
     * @param $cellPhone
     *
     * @return
     */
    public function getCellPhoneAttribute($cellPhone)
    {
        return (new StringManipulation())->formatPhoneNumber($cellPhone);
    }

    /**
     * Get Cell Phone in E164 format.
     *
     * @return
     */
    public function getCellPhoneE164Attribute()
    {
        return (new StringManipulation())->formatPhoneNumberE164($this->cell_phone);
    }

    /**
     * Get Home Phone.
     *
     * @param $homePhone
     *
     * @return
     */
    public function getHomePhoneAttribute($homePhone)
    {
        return (new StringManipulation())->formatPhoneNumber($homePhone);
    }

    /**
     * Get Home Phone in E164 format.
     *
     * @return
     */
    public function getHomePhoneE164Attribute()
    {
        return (new StringManipulation())->formatPhoneNumberE164($this->home_phone);
    }

    public function getLastEncounterAttribute($lastEncounter)
    {
        return $lastEncounter
            ? optional(Carbon::parse($lastEncounter))->toDateString()
            : null;
    }

    /**
     * Get Other Phone.
     *
     * @param $otherPhone
     *
     * @return
     */
    public function getOtherPhoneAttribute($otherPhone)
    {
        return (new StringManipulation())->formatPhoneNumber($otherPhone);
    }

    /**
     * Get Other Phone in E164 format.
     *
     * @return
     */
    public function getOtherPhoneE164Attribute()
    {
        return (new StringManipulation())->formatPhoneNumberE164($this->other_phone);
    }

    public function getPracticeNameAttribute()
    {
        return $this->practice->display_name ?? null;
    }

    /**
     * Get Other Phone.
     *
     * @param $primaryPhone
     *
     * @return
     */
    public function getPrimaryPhoneAttribute($primaryPhone)
    {
        return (new StringManipulation())->formatPhoneNumber($primaryPhone);
    }

    /**
     * Get Primary Phone in E164 format.
     *
     * @return mixed
     */
    public function getPrimaryPhoneE164Attribute()
    {
        return (new StringManipulation())->formatPhoneNumberE164($this->primary_phone);
    }

    public function getProviderFullNameAttribute()
    {
        return optional($this->provider)->getFullName();
    }

    public function getProviderInfo()
    {
        if (!$this->provider) {
            return null;
        }

        return $this->provider->providerInfo;
    }

    public function nameAndDob()
    {
        return $this->name() . ', ' . $this->dob->toDateString();
    }

    public function name()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function practice()
    {
        return $this->belongsTo(Practice::class, 'practice_id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    /**
     * @param $query
     * @param $term
     *
     * @return mixed
     */
    public function scopeSearchPhones($query, string $term)
    {
        return $query->mySQLSearch($this->phoneAttributes, $term, 'NATURAL LANGUAGE');
    }

    public function scopeSearchAddresses($query, string $term)
    {
        return $query->mySQLSearch($this->addressAttributes, $term, 'BOOLEAN', false, true);
    }

    public function scopeShouldSuggestAsFamilyForEnrollee($query, $enrolleeId)
    {
        return $query->where('id', '!=', $enrolleeId)
            ->whereNotIn('status', [
                self::CONSENTED,
                self::ENROLLED,
                self::INELIGIBLE,
                self::LEGACY,
            ])
            ->where(function ($q) {
                $q->whereDate('last_attempt_at', '<', Carbon::now()->startOfDay())
                    ->orWhereNull('last_attempt_at');
            });
    }

    public function scopeToCall($query)
    {
        //@todo add check for where phones are not all null
        return $query->where('status', self::TO_CALL);
    }

    public function scopeToSMS($query)
    {
        return $query
            ->where('status', self::TO_SMS)
            ->whereNotNull('cell_phone');
    }

    public function sendEnrollmentConsentReminderSMS()
    {
        $emjo = 'u"\U0001F31F"';

        $twilio = app(TwilioClientable::class);

        $link = url("join/{$this->invite_code}");

        $provider_name = User::find($this->provider_id)->getFullName();

        $twilio->message(
            $this->primary_phone,
            "Dr. ${provider_name} hasn’t heard from you regarding their new wellness program ${emjo}. Please enroll here: ${link}"
        );
    }

    public function sendEnrollmentConsentSMS()
    {
        $twilio = app(TwilioClientable::class);

        $link = url("join/{$this->invite_code}");
        $provider_name = User::find($this->provider_id)->getFullName();

        $twilio->message(
            $this->primary_phone,
            "Dr. ${provider_name} has invited you to their new wellness program! Please enroll here: ${link}"
        );
    }

    /**
     * Set Cell Phone.
     *
     * @param $homePhone
     */
    public function setCellPhoneAttribute($homePhone)
    {
        $this->attributes['cell_phone'] = (new StringManipulation())->formatPhoneNumberE164($homePhone);
    }

    /**
     * Set DOB.
     *
     * @param $dob
     */
    public function setDobAttribute($dob)
    {
        $this->attributes['dob'] = is_a($dob, Carbon::class)
            ? $dob
            : Carbon::parse($dob);
    }

    /**
     * Set Home Phone.
     *
     * @param $homePhone
     */
    public function setHomePhoneAttribute($homePhone)
    {
        $this->attributes['home_phone'] = (new StringManipulation())->formatPhoneNumberE164($homePhone);
    }

    /**
     * Set Other Phone.
     *
     * @param $homePhone
     */
    public function setOtherPhoneAttribute($homePhone)
    {
        $this->attributes['other_phone'] = (new StringManipulation())->formatPhoneNumberE164($homePhone);
    }

    /**
     * Set Primary Phone.
     *
     * @param $primaryPhone
     */
    public function setPrimaryPhoneNumberAttribute($primaryPhone)
    {
        $this->attributes['primary_phone'] = (new StringManipulation())->formatPhoneNumberE164($primaryPhone);
    }

    public function targetPatient()
    {
        return $this->hasOne(TargetPatient::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getPhonesE164AsString()
    {
        $phones = [];
        foreach ($this->phoneAttributes as $attribute) {
            $phones[] = $this->{$attribute . '_e164'};
        }

        return implode(', ', $phones);
    }

    public function getPhonesAsString()
    {
        $phones = [];
        foreach ($this->phoneAttributes as $attribute) {
            $phones[] = $this->{$attribute};
        }

        return collect($phones)->filter()->implode(', ');
    }

    public function getAddressesAsString()
    {
        $addresses = [];
        foreach ($this->addressAttributes as $attribute) {
            $addresses[] = $this->$attribute;
        }

        return collect($addresses)->filter()->implode(', ');
    }

    public function attachFamilyMembers($input)
    {
        if (empty($input)) {
            return false;
        }
        if (!is_array($input)) {
            $input = explode(',', $input);
        }
        foreach ($input as $id) {
            //todo: try/change to syncWithoutDetaching
            if (!$this->confirmedFamilyMembers()->where('id', $id)->exists()) {
                $this->confirmedFamilyMembers()->attach($input);
            }
        }
    }

    /**
     * Scope for patients in the system that might be the same patient (ie. duplicates)
     *
     * @param $query
     * @param User $patient
     * @param Ccda $ccda
     *
     * @return mixed
     */
    public function scopeDuplicates($query, User $patient, Ccda $ccda)
    {
        return $query->where(
            function ($q) use ($ccda, $patient) {
                $q
                    ->where('medical_record_type', get_class($ccda))
                    ->whereMedicalRecordId($ccda->id)
                    ->where('user_id', '!=', $patient->id);
            }
        )->orWhere(
            [
                [
                    'practice_id',
                    '=',
                    $patient->program_id,
                ],
                [
                    'first_name',
                    '=',
                    $patient->first_name,
                ],
                [
                    'last_name',
                    '=',
                    $patient->last_name,
                ],
                [
                    'dob',
                    '=',
                    $patient->getBirthDate(),
                ],
            ]
        )->orWhere(
            [
                [
                    'practice_id',
                    '=',
                    $patient->program_id,
                ],
                [
                    'mrn',
                    '=',
                    $patient->getMRN(),
                ],
            ]
        );
    }
}


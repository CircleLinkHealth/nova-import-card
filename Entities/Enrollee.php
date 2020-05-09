<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Entities;

use App\Contracts\Services\TwilioClientable;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Core\Filters\Filterable;
use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Core\Traits\MySQLSearchable;
use CircleLinkHealth\Core\Traits\Notifiable;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\HasEnrollableInvitation;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Support\Str;

/**
 * CircleLinkHealth\Eligibility\Entities\Enrollee.
 *
 * @property int                                               $id
 * @property string|null                                       $medical_record_type
 * @property int|null                                          $medical_record_id
 * @property int|null                                          $user_id
 * @property int|null                                          $provider_id
 * @property int|null                                          $practice_id
 * @property int|null                                          $care_ambassador_id
 * @property int                                               $total_time_spent
 * @property string|null                                       $last_call_outcome
 * @property string|null                                       $last_call_outcome_reason
 * @property string                                            $mrn
 * @property string                                            $first_name
 * @property string                                            $last_name
 * @property string                                            $address
 * @property string                                            $address_2
 * @property string                                            $city
 * @property string                                            $state
 * @property string                                            $zip
 * @property mixed                                             $primary_phone
 * @property string                                            $other_phone
 * @property string                                            $home_phone
 * @property string                                            $cell_phone
 * @property \Carbon\Carbon|null                               $dob
 * @property string                                            $lang
 * @property string                                            $invite_code
 * @property string                                            $status
 * @property int                                               $attempt_count
 * @property string|null                                       $preferred_days
 * @property string|null                                       $preferred_window
 * @property string|null                                       $invite_sent_at
 * @property string|null                                       $consented_at
 * @property string|null                                       $last_attempt_at
 * @property string|null                                       $invite_opened_at
 * @property \Carbon\Carbon|null                               $created_at
 * @property \Carbon\Carbon|null                               $updated_at
 * @property \Carbon\Carbon|null                               $requested_callback
 * @property string                                            $primary_insurance
 * @property string                                            $secondary_insurance
 * @property string                                            $tertiary_insurance
 * @property int|null                                          $has_copay
 * @property string                                            $email
 * @property string                                            $last_encounter
 * @property string                                            $referring_provider_name
 * @property int|null                                          $confident_provider_guess
 * @property string                                            $problems
 * @property int                                               $cpm_problem_1
 * @property int                                               $cpm_problem_2
 * @property string|null                                       $color
 * @property \App\CareAmbassador|null                          $careAmbassador
 * @property mixed                                             $practice_name
 * @property mixed                                             $provider_full_name
 * @property \CircleLinkHealth\Customer\Entities\Practice|null $practice
 * @property \CircleLinkHealth\Customer\Entities\User|null     $provider
 * @property mixed                                             $primary_phone_number
 * @property \CircleLinkHealth\Customer\Entities\User|null     $user
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee toCall()
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee toSMS()
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereAddress($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereAddress2($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereAttemptCount($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereCareAmbassadorId($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereCellPhone($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereCity($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereColor($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereConfidentProviderGuess($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereConsentedAt($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereCpmProblem1($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereCpmProblem2($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereCreatedAt($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereDob($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereEmail($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereFirstName($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereHasCopay($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereHomePhone($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereId($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereInviteCode($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereInviteOpenedAt($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereInviteSentAt($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereLang($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereLastAttemptAt($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereLastCallOutcome($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereLastCallOutcomeReason($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereLastEncounter($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereLastName($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereMedicalRecordId($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereMedicalRecordType($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereMrn($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereOtherPhone($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee wherePracticeId($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee wherePreferredDays($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee wherePreferredWindow($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee wherePrimaryInsurance($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee wherePrimaryPhone($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereProblems($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereProviderId($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereReferringProviderName($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereSecondaryInsurance($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereState($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereStatus($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereTertiaryInsurance($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereTotalTimeSpent($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereUpdatedAt($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereUserId($value)
 * @method   static                                            \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereZip($value)
 * @mixin \Eloquent
 * @property int|null                                                   $batch_id
 * @property int|null                                                   $eligibility_job_id
 * @property int|null                                                   $care_ambassador_user_id
 * @property \CircleLinkHealth\Eligibility\Entities\EligibilityJob|null $eligibilityJob
 * @property $cell_phone_e164
 * @property $home_phone_e164
 * @property $other_phone_e164
 * @property mixed $primary_phone_e164
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection
 *     $revisionHistory
 * @property \CircleLinkHealth\Eligibility\Entities\TargetPatient $targetPatient
 * @method   static                                               \Illuminate\Database\Eloquent\Builder|\App\Enrollee filter(\App\Filters\QueryFilters $filters)
 * @method   static                                               \Illuminate\Database\Eloquent\Builder|\App\Enrollee newModelQuery()
 * @method   static                                               \Illuminate\Database\Eloquent\Builder|\App\Enrollee newQuery()
 * @method   static                                               \Illuminate\Database\Eloquent\Builder|\App\Enrollee query()
 * @method   static                                               \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereBatchId($value)
 * @method   static                                               \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereCareAmbassadorUserId($value)
 * @method   static                                               \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereEligibilityJobId($value)
 * @method   static                                               \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereSoftRejectedCallback($value)
 * @method   static                                               \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereRequestedCallback($value)
 * @property int|null                                             $revision_history_count
 * @property array|null                                           $agent_details
 * @property mixed                                                $agent
 * @method   static                                               \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereAgentDetails($value)
 * @property int|null                                             $family_enrollee_id
 * @method   static                                               \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereFamilyEnrolleeId($value)
 * @property string|null                                          $soft_rejected_callback
 * @property \CircleLinkHealth\Eligibility\Entities\Enrollee[]|\Illuminate\Database\Eloquent\Collection
 *     $confirmedFamilyMembers
 * @property int|null $confirmed_family_members_count
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee
 *     mySQLSearch($columns, $term, $mode = 'BOOLEAN', $shouldRequireAll = true, $shouldRequireIntegers = true)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee
 *     searchAddresses($term)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee
 *     searchPhones($term)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee
 *     shouldSuggestAsFamilyForEnrollee($enrolleeId)
 * @property int|null                                                                                                        $location_id
 * @property \CircleLinkHealth\SharedModels\Entities\Ccda|null                                                               $ccda
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee duplicates(\CircleLinkHealth\Customer\Entities\User $patient, \CircleLinkHealth\SharedModels\Entities\Ccda $ccda)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee whereLocationId($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee hasPhone($phone)
 * @property string|null                                                                                                     $other_note
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee shouldBeCalled()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee lessThanThreeAttempts()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\Enrollee withCaPanelRelationships()
 * @property string|null                                                                                                     $source
 * @property int|null                                                                                                        $enrollment_non_responsive
 * @property int                                                                                                             $auto_enrollment_triggered
 * @property \CircleLinkHealth\Customer\EnrollableInvitationLink\EnrollableInvitationLink|null                               $enrollmentInvitationLink
 * @property \CircleLinkHealth\Core\Entities\DatabaseNotification[]|\Illuminate\Notifications\DatabaseNotificationCollection $notifications
 * @property int|null                                                                                                        $notifications_count
 * @property \CircleLinkHealth\Customer\EnrollableRequestInfo\EnrollableRequestInfo|null                                     $statusRequestsInfo
 */
class Enrollee extends BaseModel
{
    use Filterable;
    use HasEnrollableInvitation;
    use MySQLSearchable;
    use Notifiable;

    // Agent array keys
    const AGENT_EMAIL_KEY        = 'email';
    const AGENT_NAME_KEY         = 'name';
    const AGENT_PHONE_KEY        = 'phone';
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
     * Enrollees who did not respond to any of our notifications to enroll.
     */
    const NON_RESPONSIVE = 'non_responsive';

    /**
     * Queued for auto-enrollment.
     */
    const QUEUE_AUTO_ENROLLMENT = 'queue_auto_enrollment';

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
     * status = consented.
     */
    const TO_CONFIRM_CONSENTED = 'to_confirm_consented';

    /**
     * status = unreachable.
     */
    const TO_CONFIRM_REJECTED = 'to_confirm_rejected';

    /**
     * status = unreachable.
     */
    const TO_CONFIRM_SOFT_REJECTED = 'to_confirm_soft_rejected';

    /**
     * For confirmed family members
     * We are setting their statuses at the time of the status update of the initial/original
     * Family member, so as to pre-fill their data according to the initial/original family member.
     */
    const TO_CONFIRM_STATUSES = [
        self::TO_CONFIRM_CONSENTED,
        self::TO_CONFIRM_REJECTED,
        self::TO_CONFIRM_SOFT_REJECTED,
        self::TO_CONFIRM_UNREACHABLE,
    ];

    /**
     * status = unreachable.
     */
    const TO_CONFIRM_UNREACHABLE = 'to_confirm_utc';

    /**
     * status = to_sms.
     */
    const TO_SMS = 'sms_queue';

    /**
     * status = utc.
     */
    const UNREACHABLE         = 'utc';
    const UNREACHABLE_PATIENT = 'unreachable_patient';

    /**
     * For field: source.
     *
     * Csv with enrollees uploaded through Superadmin page
     */
    const UPLOADED_CSV = 'uploaded-csv';

    /**
     * For mySql full-text search.
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
        'dob',
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

    /**
     * For mySql full-text search.
     *
     * @var array
     */
    public $phoneAttributes = [
        'cell_phone',
        'home_phone',
        'other_phone',
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
        'other_note',

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

        'enrollment_non_responsive',
        'auto_enrollment_triggered',
        'source',
    ];

    protected $table = 'enrollees';

    public function attachFamilyMembers($input)
    {
        if (empty($input)) {
            return false;
        }
        if ( ! is_array($input)) {
            $input = explode(',', $input);
        }
        foreach ($input as $id) {
            //todo: try/change to syncWithoutDetaching
            if ( ! $this->confirmedFamilyMembers()->where('id', $id)->exists()) {
                $this->confirmedFamilyMembers()->attach($input);
            }
        }
    }

    public function careAmbassador()
    {
        return $this->belongsTo(User::class, 'care_ambassador_user_id');
    }

    public function ccda()
    {
        return $this->belongsTo(Ccda::class, 'medical_record_id');
    }

    public function confirmedFamilyMembers()
    {
        return $this->belongsToMany(
            Enrollee::class,
            'enrollee_family_members',
            'enrollee_id',
            'family_member_enrollee_id'
        )->withTimestamps();
    }

    public function eligibilityJob()
    {
        return $this->belongsTo(EligibilityJob::class);
    }

    public function getAddressesAsString(Enrollee $compareAgainstEnrollee = null)
    {
        $addresses = [];
        foreach ($this->addressAttributes as $attribute) {
            $attr = trim($this->$attribute);

            if (empty($attr)) {
                continue;
            }

            if ($compareAgainstEnrollee) {
                $shouldHighlight = false;

                if ( ! empty(trim($compareAgainstEnrollee->address))) {
                    $shouldHighlight = levenshtein($attr, $compareAgainstEnrollee->address) <= 7;
                }

                if ( ! $shouldHighlight && ! empty(trim($compareAgainstEnrollee->address_2))) {
                    $shouldHighlight = levenshtein($attr, $compareAgainstEnrollee->address_2) <= 7;
                }

                if ($shouldHighlight) {
                    //if it matches, highlight it.
                    $attr = "<span style='background-color: #4fb2e2; color: white; padding-left: 5px; padding-right: 5px; border-radius: 3px;'>{$attr}</span>";
                }
            }

            $addresses[] = $attr;
        }

        return collect($addresses)->filter()->implode(', ');
    }

    /**
     * @param mixed $key
     */
    public function getAgentAttribute($key)
    {
        if (empty($this->agent_details)) {
            return null;
        }

        if ( ! array_key_exists($key, $this->agent_details)) {
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

    public static function getEquivalentToConfirmStatus($status)
    {
        return collect(self::TO_CONFIRM_STATUSES)->filter(
            function ($toConfirmStatus) use ($status) {
                return Str::endsWith($toConfirmStatus, $status);
            }
        )->first();
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

    public function getPhonesAsString(Enrollee $compareAgainstEnrollee = null)
    {
        $phones = [];
        foreach ($this->phoneAttributes as $attribute) {
            $attr = $this->$attribute;
            if ($compareAgainstEnrollee) {
                if (in_array(
                    $attr,
                    [
                        $compareAgainstEnrollee->home_phone,
                        $compareAgainstEnrollee->cell_phone,
                        $compareAgainstEnrollee->other_phone,
                    ]
                )
                ) {
                    //if it matches, highlight it.
                    $attr = "<span style='background-color: #26a69a; color: white; padding-left: 5px; padding-right: 5px; border-radius: 3px;'>{$attr}</span>";
                }
            }
            $phones[] = trim($attr);
        }

        return collect($phones)->filter()->implode(', ');
    }

    public function getPhonesE164AsString()
    {
        $phones = [];
        foreach ($this->phoneAttributes as $attribute) {
            $phones[] = $this->{$attribute.'_e164'};
        }

        return implode(', ', $phones);
    }

    public function getPracticeNameAttribute()
    {
        return $this->practice->display_name ?? null;
    }

    public function getPreferredCallDays()
    {
        if (empty($this->enrollee->preferred_days)) {
            return null;
        }

        return parseCallDays($this->enrollee->preferred_days);
    }

    public function getPreferredCallTimes()
    {
        if (empty($this->enrollee->preferred_window)) {
            return null;
        }

        return parseCallTimes($this->enrollee->preferred_window);
    }

    public function getPreferredPhoneType()
    {
        if (empty(trim($this->primary_phone_e164))) {
            return '';
        }

        $phones = [
            $this->home_phone_e164  => 'home',
            $this->cell_phone_e164  => 'cell',
            $this->other_phone_e164 => 'other',
            //agent phones always saved as e164
            $this->getAgentAttribute(self::AGENT_PHONE_KEY) => 'agent',
        ];

        $preferredPhone = isset($phones[$this->primary_phone_e164]) ? $phones[$this->primary_phone_e164] : null;

        //edge case - add primary as other phone
        if ( ! $preferredPhone) {
            $this->other_phone = $this->primary_phone_e164;
            $this->save();
            $preferredPhone = 'other';
        }

        return $preferredPhone;
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
        if ( ! $this->provider) {
            return null;
        }

        return $this->provider->providerInfo;
    }

    public function name()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function nameAndDob()
    {
        return $this->name().', '.$this->dob->toDateString();
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
     * Scope for patients in the system that might be the same patient (ie. duplicates).
     *
     * @param $query
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

    /**
     * Assume DB format (e164).
     * For typeahead searching.
     *
     * @param $query
     * @param $phone
     */
    public function scopeHasPhone($query, $phone)
    {
        if (Str::contains($phone, '-')) {
            $phone = str_replace('-', '', $phone);
        }

        return $query->where(
            function ($q) use ($phone) {
                $q->where('home_phone', 'like', "%${phone}%")
                    ->orWhere('cell_phone', 'like', "%${phone}%")
                    ->orWhere('other_phone', 'like', "%${phone}%")
                    ->orWhere('primary_phone', 'like', "%${phone}%");
            }
        );
    }

    public function scopeLessThanThreeAttempts($query)
    {
        $query->where(function ($q) {
            $q->whereNull('attempt_count')
                ->orWhere('attempt_count', '<', 3);
        });
    }

    public function scopeSearchAddresses($query, string $term)
    {
        return $query->mySQLSearch($this->addressAttributes, $term, 'BOOLEAN', false, true, true);
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

    public function scopeShouldBeCalled($query)
    {
        $canBeCalledStatuses = array_merge([
            self::TO_CALL,
            self::UNREACHABLE,
        ], self::TO_CONFIRM_STATUSES);

        return $query->whereIn(
            'status',
            $canBeCalledStatuses
        )
            ->where(function ($q) {
                $q->whereNull('attempt_count')
                    ->orWhere('attempt_count', '<', 3);
            });
    }

    public function scopeShouldSuggestAsFamilyForEnrollee($query, $enrolleeId)
    {
        return $query->where('id', '!=', $enrolleeId)
            ->whereNotIn(
                'status',
                [
                    self::CONSENTED,
                    self::ENROLLED,
                    self::INELIGIBLE,
                    self::LEGACY,
                ]
            )
            ->where(
                function ($q) {
                    $q->whereDate('last_attempt_at', '<', Carbon::now()->startOfDay())
                        ->orWhereNull('last_attempt_at');
                }
            );
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

    public function scopeWithCaPanelRelationships($query)
    {
        return $query->with(['practice.enrollmentTips', 'provider.providerInfo', 'confirmedFamilyMembers']);
    }

    public function selfEnrollmentStatuses()
    {
        return $this->hasOne(SelfEnrollmentStatus::class, 'enrollee_id');
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

        $link          = url("join/{$this->invite_code}");
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

    public function speaksSpanish()
    {
        return stringMeansSpanish($this->lang);
    }

    public static function statusIsToConfirm($status)
    {
        return in_array($status, self::TO_CONFIRM_STATUSES);
    }

    public function targetPatient()
    {
        return $this->hasOne(TargetPatient::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use App\Call;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Core\Filters\Filterable;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CircleLinkHealth\Customer\Entities\Patient.
 *
 * @property int $id
 * @property int|null $imported_medical_record_id
 * @property int $user_id
 * @property int|null $ccda_id
 * @property int|null $care_plan_id
 * @property string|null $active_date
 * @property string|null $agent_name
 * @property string|null $agent_telephone
 * @property string|null $agent_email
 * @property string|null $agent_relationship
 * @property string|null $birth_date
 * @property string|null $ccm_status
 * @property string|null $consent_date
 * @property string|null $gender
 * @property \Carbon\Carbon|null $date_paused
 * @property \Carbon\Carbon|null $date_withdrawn
 * @property string|null $mrn_number
 * @property string|null $preferred_cc_contact_days
 * @property string|null $preferred_contact_language
 * @property string|null $preferred_contact_location
 * @property string|null $preferred_contact_method
 * @property string|null $preferred_contact_time
 * @property string|null $preferred_contact_timezone
 * @property string|null $registration_date
 * @property string|null $daily_reminder_optin
 * @property string|null $daily_reminder_time
 * @property string|null $daily_reminder_areas
 * @property string|null $hospital_reminder_optin
 * @property string|null $hospital_reminder_time
 * @property string|null $hospital_reminder_areas
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string|null $deleted_at
 * @property string $general_comment
 * @property int $preferred_calls_per_month
 * @property string $last_successful_contact_time
 * @property int|null $no_call_attempts_since_last_success
 * @property string $last_contact_time
 * @property string $daily_contact_window_start
 * @property string $daily_contact_window_end
 * @property int|null $next_call_id
 * @property int|null $family_id
 * @property string|null $date_welcomed
 * @property \CircleLinkHealth\Customer\Entities\Family|null $family
 * @property mixed $address
 * @property mixed $city
 * @property mixed $first_name
 * @property mixed $last_name
 * @property mixed $state
 * @property mixed $zip
 * @property \CircleLinkHealth\Customer\Entities\PatientContactWindow[]|\Illuminate\Database\Eloquent\Collection
 *     $contactWindows
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 * @property \CircleLinkHealth\Customer\Entities\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient enrolled()
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient hasFamily()
 * @method static \Illuminate\Database\Query\Builder|\App\Patient onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereActiveDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereAgentEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereAgentName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereAgentRelationship($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereAgentTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereCarePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereCcdaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereCcmStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereConsentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereCurMonthActivityTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereDailyContactWindowEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereDailyContactWindowStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereDailyReminderAreas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereDailyReminderOptin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereDailyReminderTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereDatePaused($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereDateWelcomed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereDateWithdrawn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereFamilyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereGeneralComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereHospitalReminderAreas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereHospitalReminderOptin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereHospitalReminderTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereImportedMedicalRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereLastContactTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereLastSuccessfulContactTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereMrnNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereNextCallId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereNoCallAttemptsSinceLastSuccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient wherePreferredCallsPerMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient wherePreferredCcContactDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient wherePreferredContactLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient wherePreferredContactLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient wherePreferredContactMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient wherePreferredContactTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient wherePreferredContactTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereRegistrationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Patient withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Patient withoutTrashed()
 * @mixin \Eloquent
 * @property \Illuminate\Support\Carbon|null $paused_letter_printed_at
 * @property \Illuminate\Support\Carbon|null $date_unreachable
 * @property mixed $last_call_status
 * @property \CircleLinkHealth\Customer\Entities\Location|null $location
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Patient byStatus($fromDate, $toDate)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Patient ccmStatus($status, $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Patient
 *     filter(\App\Filters\QueryFilters $filters)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Patient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Patient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Patient query()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Patient
 *     whereDateUnreachable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Patient
 *     wherePausedLetterPrintedAt($value)
 * @property string|null $withdrawn_reason
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Patient enrolledOrPaused()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Patient
 *     whereWithdrawnReason($value)
 * @property-read int|null $contact_windows_count
 * @property-read \CircleLinkHealth\Customer\Entities\User|null $nurse
 * @property-read int|null $revision_history_count
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Patient
 *     whereNurseUserId($value)
 * @property int $is_awv
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Patient whereIsAwv($value)
 */
class Patient extends BaseModel
{
    use Filterable;
    use SoftDeletes;
    const BHI_CONSENT_NOTE_TYPE = 'Consented to BHI';
    const BHI_REJECTION_NOTE_TYPE = 'Did Not Consent to BHI';

    /**
     * Starting on this date, when a patients consents for CCM, they also consent for BHI.
     *
     * Patients who consented before this date need to have a separate BHI consent. Separate BHI consent is denoted by
     * the patient having a Note with type "BHI Consent". This affects only Patients who consent to receiving BHI
     * services. As of 07/23/2018, there exist ~200 BHI eligible patients who have consented before 07/23/2018.
     */
    const DATE_CONSENT_INCLUDES_BHI = '2018-07-23 00:00:00';

    /**
     * Available Patient Statuses.
     */
    const ENROLLED = 'enrolled';
    const PATIENT_REJECTED = 'patient_rejected';
    const PAUSED = 'paused';
    const TO_ENROLL = 'to_enroll';
    const UNREACHABLE = 'unreachable';
    const WITHDRAWN = 'withdrawn';

    public $phi = [
        'agent_name',
        'agent_telephone',
        'agent_email',
        'birth_date',
        'gender',
        'mrn_number',
        'general_comment',
    ];

    protected $dates = [
        'birth_date',
        'consent_date',
        'date_withdrawn',
        'date_paused',
        'date_unreachable',
        'paused_letter_printed_at',
    ];

    protected $fillable = [
        'imported_medical_record_id',
        'user_id',
        'ccda_id',
        'care_plan_id',
        'active_date',
        'agent_name',
        'agent_telephone',
        'agent_email',
        'agent_relationship',
        'birth_date',
        'ccm_status',
        'paused_letter_printed_at',
        'consent_date',
        'gender',
        'date_paused',
        'date_withdrawn',
        'withdrawn_reason',
        'date_unreachable',
        'mrn_number',
        'preferred_cc_contact_days',
        'preferred_contact_language',
        'preferred_contact_location',
        'preferred_contact_method',
        'preferred_contact_time',
        'preferred_contact_timezone',
        'registration_date',
        'daily_reminder_optin',
        'daily_reminder_time',
        'daily_reminder_areas',
        'hospital_reminder_optin',
        'hospital_reminder_time',
        'hospital_reminder_areas',
        'created_at',
        'updated_at',
        'deleted_at',
        'general_comment',
        'preferred_calls_per_month',
        'last_successful_contact_time',
        'no_call_attempts_since_last_success',
        'last_contact_time',
        'daily_contact_window_start',
        'daily_contact_window_end',
        'next_call_id',
        'family_id',
        'date_welcomed',
        'is_awv'
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'patient_info';

    /**
     * Import Patient's Call Window from the sheet, or save default.
     *
     * @param array $days | eg. [1,2,3] Monday is 1
     * @param $fromTime | eg. '09:00:00'
     * @param $toTime | eg. '17:00:00'
     *
     * @return array of PatientContactWindows
     */
    public function attachNewOrDefaultCallWindows(
        array $days = [],
        $fromTime = null,
        $toTime = null
    ) {
        $daysNumber = [
            1,
            2,
            3,
            4,
            5,
        ];

        if ( ! empty($days)) {
            $daysNumber = $days;
        }

        $timeFrom = '09:00:00';
        $timeTo   = '17:00:00';

        if ( ! empty($fromTime)) {
            $timeFrom = Carbon::parse($fromTime)->format('H:i:s');
        }
        if ( ! empty($toTime)) {
            $timeTo = Carbon::parse($toTime)->format('H:i:s');
        }

        return PatientContactWindow::sync(
            $this,
            $daysNumber,
            $timeFrom,
            $timeTo
        );
    }

    public function contactWindows()
    {
        return $this->hasMany(PatientContactWindow::class, 'patient_info_id');
    }

    public function dob()
    {
        return Carbon::parse($this->birth_date)->format('m/d/Y');
    }

    public function family()
    {
        return $this->belongsTo(Family::class, 'family_id');
    }

    public function getAddressAttribute()
    {
        return $this->user->address;
    }

    public function getCcmTime()
    {
        return $this->user->getCcmTime();
    }

    public function getCityAttribute()
    {
        return $this->user->city;
    }

    public function getContactWindowsString()
    {
        $windows = [];

        foreach ($this->contactWindows as $window) {
            $start = Carbon::parse($window->window_time_start)->format('h:i a');
            $end   = Carbon::parse($window->window_time_end)->format('h:i a');
            switch ($window->day_of_week) {
                case 1:
                    $windows[] = "Monday: {$start} - {$end}<br/>";
                    break;
                case 2:
                    $windows[] = "Tuesday: {$start} - {$end}<br/>";
                    break;
                case 3:
                    $windows[] = "Wednesday: {$start} - {$end}<br/>";
                    break;
                case 4:
                    $windows[] = "Thursday: {$start} - {$end}<br/>";
                    break;
                case 5:
                    $windows[] = "Friday: {$start} - {$end}<br/>";
                    break;
                case 6:
                    $windows[] = "Saturday: {$start} - {$end}<br/>";
                    break;
                case 7:
                    $windows[] = "Sunday: {$start} - {$end}<br/>";
                    break;
            }
        }

        return empty($windows)
            ? 'Patient call date/time preferences not found.'
            : implode($windows);
    }

    /**
     * Get family members of a patient.
     * TODO: remove patient argument, since its a function of the Patient class. Or, make it a static function.
     *
     * @param Patient $patient
     *
     * @return array|static
     */
    public function getFamilyMembers(Patient $patient)
    {
        $family = $patient->family;

        if (is_object($family)) {
            $members = $family->patients()->get();

            //remove the patient from the family itself
            return $members->reject(function ($item) {
                return $item->id == $this->id;
            });
        }

        return [];
    }

    public function getFirstNameAttribute()
    {
        return $this->user->getFirstName();
    }

    public function getFullName()
    {
        return $this->user->getFullName();
    }

    public function getLastCallStatusAttribute()
    {
        if (is_null($this->no_call_attempts_since_last_success)) {
            return 'n/a';
        }

        if ($this->no_call_attempts_since_last_success > 0) {
            return $this->no_call_attempts_since_last_success . 'x Attempts';
        }

        return 'Success';
    }

    public function getLastNameAttribute()
    {
        return $this->user->getLastName();
    }

    public function getPreferences()
    {
        $patientTimezone = $this->user->timezone;
        if ( ! isset($patientTimezone)) {
            $patientTimezone = 'America/New_York';
        }
        $tzAbbr = Carbon::now()->setTimezone($patientTimezone)->format('T');

        return [
            'calls_per_month'  => $this->preferred_calls_per_month,
            'contact_timezone' => $tzAbbr,

            'contact_language' => $this->preferred_contact_language,
            'contact_method'   => $this->preferred_contact_method,
            'contact_window'   => $this->contactWindows,
            'contact_location' => $this->location,
        ];
    }

    public function getStateAttribute()
    {
        return $this->user->state;
    }

    public function getZipAttribute()
    {
        return $this->user->zip;
    }

    public function hasFamilyId()
    {
        return null != $this->family_id;
    }

    public function lastNurseThatPerformedActivity()
    {
        $id = \CircleLinkHealth\TimeTracking\Entities\Activity::where('patient_id', $this->user_id)
                                                              ->whereHas('provider', function ($q) {
                                                                  $q->ofType('care-center');
                                                              })
                                                              ->orderBy('created_at', 'desc')
                                                              ->first()['provider_id'];

        return Nurse::where('user_id', $id)->first();
    }

    public function lastReachedNurse()
    {
        return Call::where('inbound_cpm_id', $this->user_id)
                   ->whereNotNull('called_date')
                   ->orderBy('called_date', 'desc')
                   ->first()['outbound_cpm_id'];
    }

    /**
     * Get the patient's Location.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location()
    {
        return $this->belongsTo(Location::class, 'preferred_contact_location');
    }

    public static function numberToTextDaySwitcher($string)
    {
        $mapper = function ($i) {
            switch ($i) {
                case 1:
                    return ' Mon';
                    break;
                case 2:
                    return ' Tue';
                    break;
                case 3:
                    return ' Wed';
                    break;
                case 4:
                    return ' Thu';
                    break;
                case 5:
                    return ' Fri';
                    break;
                case 6:
                    return ' Sat';
                    break;
                case 7:
                    return ' Sun';
                    break;
            }

            return '';
        };

        $days = explode(',', $string);

        $formatted = array_map($mapper, $days);

        return implode(',', $formatted);
    }

    /**
     * Returns nurseInfos that have:
     *  - a call window in the future
     *  - location intersection with the patient's preferred contact location.
     */
    public function nursesThatCanCareforPatient()
    {
        //Get user's programs

        $nurses = Nurse::whereHas('user', function ($q) {
            $q->where('user_status', 1);
        })->get();

        //Result array with Nurses
        $result = [];

        foreach ($nurses as $nurse) {
            //get all locations for nurse
            $nurse_programs = $nurse->user->viewableProgramIds();

            $intersection = in_array($this->user->program_id, $nurse_programs);

//            to optimize further, check whether the nurse has any windows upcoming
//                $future_windows = $nurse->windows->where('date', '>', Carbon::now()->toDateTimeString());

            //check if they can care for patient AND if they have a window.
            if ($intersection) { //&& $future_windows->count() > 0
                $result[] = $nurse->user_id;
            }
        }

        return $result;
    }

    public function safe()
    {
        return [
            'id'               => $this->id,
            'user_id'          => $this->user_id,
            'ccm_status'       => $this->ccm_status,
            'birth_date'       => presentDate($this->birth_date, false),
            'age'              => $this->birth_date
                ? (Carbon::now()->year - Carbon::parse($this->birth_date)->year)
                : 0,
            'gender'           => $this->gender,
            'mrn_number'       => $this->mrn_number,
            'date_paused'      => optional($this->date_paused)->format('c'),
            'date_withdrawn'   => optional($this->date_withdrawn)->format('c'),
            'date_unreachable' => optional($this->date_unreachable)->format('c'),
            'withdrawn_reason' => $this->withdrawn_reason,
            'created_at'       => optional($this->created_at)->format('c'),
            'updated_at'       => optional($this->updated_at)->format('c'),
        ];
    }

    public function scopeByStatus($query, $fromDate, $toDate)
    {
        return $query->where(function ($query) use ($fromDate, $toDate) {
            $query->where(function ($subQuery) use ($fromDate, $toDate) {
                $subQuery->ccmStatus(Patient::PAUSED)
                         ->where([
                             ['date_paused', '>=', $fromDate],
                             ['date_paused', '<=', $toDate],
                         ]);
            })
                  ->orWhere(function ($subQuery) use ($fromDate, $toDate) {
                      $subQuery->ccmStatus(Patient::WITHDRAWN)
                               ->where([
                                   ['date_withdrawn', '>=', $fromDate],
                                   ['date_withdrawn', '<=', $toDate],
                               ]);
                  })
                  ->orWhere(function ($subQuery) use ($fromDate, $toDate) {
                      $subQuery->ccmStatus(Patient::ENROLLED)
                               ->where([
                                   ['registration_date', '>=', $fromDate],
                                   ['registration_date', '<=', $toDate],
                               ]);
                  });
        });
    }

    /**
     * Scope by ccm_status.
     *
     * @param $builder
     * @param $status
     * @param string $operator
     */
    public function scopeCcmStatus($builder, $status, $operator = '=')
    {
        $builder->where('ccm_status', $operator, $status);
    }

    public function scopeEnrolled($query)
    {
        return $query->where('ccm_status', Patient::ENROLLED);
    }

    public function scopeEnrolledOrPaused($query)
    {
        return $query->whereIn('ccm_status', [Patient::ENROLLED, Patient::PAUSED]);
    }

    public function scopeHasFamily($query)
    {
        return $query->whereNotNull('family_id');
    }

    public function setAddressAttribute($value)
    {
        $this->user->address = $value;
        $this->user->save();

        return true;
    }

    public function setCcmStatusAttribute($value)
    {
        $statusBefore                   = $this->ccm_status;
        $this->attributes['ccm_status'] = $value;

        if ($statusBefore !== $value) {
            if (Patient::ENROLLED == $value) {
                $this->attributes['registration_date'] = Carbon::now()->toDateTimeString();
            }
            if (Patient::PAUSED == $value) {
                $this->attributes['date_paused'] = Carbon::now()->toDateTimeString();
            }
            if (Patient::WITHDRAWN == $value) {
                $this->attributes['date_withdrawn'] = Carbon::now()->toDateTimeString();
            }
            if (Patient::UNREACHABLE == $value) {
                $this->attributes['date_unreachable'] = Carbon::now()->toDateTimeString();
            }
        }
        $this->save();
    }

    public function setCityAttribute($value)
    {
        $this->user->city = $value;
        $this->user->save();

        return true;
    }

    public function setFirstNameAttribute($value)
    {
        $this->user->setFirstName($value);
        $this->user->save();

        return true;
    }

    public function setLastNameAttribute($value)
    {
        $this->user->setLastName($value);
        $this->user->save();

        return true;
    }

    public function setStateAttribute($value)
    {
        $this->user->state = $value;
        $this->user->save();

        return true;
    }

    public function setZipAttribute($value)
    {
        $this->user->zip = $value;
        $this->user->save();

        return true;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Returns current nurse of patient (could be temporary or permanent
     *
     * @return User|null
     */
    public function getNurse()
    {
        /** @var PatientNurse $record */
        $record = PatientNurse::where('patient_user_id', '=', $this->user_id)
                              ->with('nurse')
                              ->first();

        if ( ! $record) {
            return null;
        }

        if ( ! $record->nurse) {
            return null;
        }

        return $record->nurse;
    }

    /**
     * Get current nurse (could be parmanent or temporary) and upcoming (temporary)
     */
    public function getNurses()
    {
        /** @var PatientNurse $record */
        $record = PatientNurse::where('patient_user_id', '=', $this->user_id)
                              ->with(['permanentNurse', 'temporaryNurse'])
                              ->first();

        if ( ! $record) {
            return null;
        }

        $result = [];
        if ($record->permanentNurse) {
            $result['permanent'] = [
                'user' => $record->permanentNurse,
            ];
        }

        $now = Carbon::now();
        if ($record->temporaryNurse && $now->isBefore($record->temporary_to)) {
            $result['temporary'] = [
                'from' => $record->temporary_from,
                'to'   => $record->temporary_to,
                'user' => $record->temporaryNurse,
            ];
        }

        return empty($result)
            ? null
            : $result;
    }
}

<?php namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Patient
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
 * @property string|null $cur_month_activity_time
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
 * @property-read \App\Family|null $family
 * @property mixed $address
 * @property mixed $city
 * @property-read mixed $current_month_c_c_m_time
 * @property mixed $first_name
 * @property mixed $last_name
 * @property mixed $state
 * @property mixed $zip
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientMonthlySummary[] $monthlySummaries
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientContactWindow[] $contactWindows
 * @property-read \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 * @property-read \App\User $user
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
 */
class Patient extends \App\BaseModel
{

    use SoftDeletes;
    use \Venturecraft\Revisionable\RevisionableTrait;

    protected $dates = [
        'date_withdrawn',
        'date_paused',
    ];

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql';
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'patient_info';
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    protected $guarded = [];

    public static function boot()
    {
        parent::boot();
    }

    // START RELATIONSHIPS

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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function contactWindows()
    {
        return $this->hasMany(PatientContactWindow::class, 'patient_info_id', 'id');
    }

    public function monthlySummaries()
    {
        return $this->hasMany(PatientMonthlySummary::class, 'patient_info_id', 'id');
    }

    // END RELATIONSHIPS


    // START ATTRIBUTES

    // first_name

    public function family()
    {

        return $this->belongsTo(Family::class, 'family_id');
    }

    public function getFirstNameAttribute()
    {
        return $this->user->first_name;
    }

    // last_name

    public function setFirstNameAttribute($value)
    {
        $this->user->first_name = $value;
        $this->user->save();

        return true;
    }

    public function getLastNameAttribute()
    {
        return $this->user->last_name;
    }

    // address

    public function setLastNameAttribute($value)
    {
        $this->user->last_name = $value;
        $this->user->save();

        return true;
    }

    public function getAddressAttribute()
    {
        return $this->user->address;
    }

    // city

    public function setAddressAttribute($value)
    {
        $this->user->address = $value;
        $this->user->save();

        return true;
    }

    public function getCityAttribute()
    {
        return $this->user->city;
    }

    // state

    public function setCityAttribute($value)
    {
        $this->user->city = $value;
        $this->user->save();

        return true;
    }

    public function getStateAttribute()
    {
        return $this->user->state;
    }

    // zip

    public function setStateAttribute($value)
    {
        $this->user->state = $value;
        $this->user->save();

        return true;
    }

    public function getZipAttribute()
    {
        return $this->user->zip;
    }

    public function setZipAttribute($value)
    {
        $this->user->zip = $value;
        $this->user->save();

        return true;
    }

    public function setCcmStatusAttribute($value)
    {
        $statusBefore = $this->ccm_status;
        $this->attributes['ccm_status'] = $value;
        // update date tracking
        if ($statusBefore !== $value) {
            if ($value == 'paused') {
                $this->attributes['date_paused'] = date("Y-m-d H:i:s");
            };
            if ($value == 'withdrawn') {
                $this->attributes['date_withdrawn'] = date("Y-m-d H:i:s");
            };
        }
        $this->save();

        return true;
    }


    // Return s current months CCM time formatted for UI

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

    public function getCurrentMonthCCMTimeAttribute()
    {
        $seconds = $this->cur_month_activity_time;
        $H = floor($seconds / 3600);
        $i = ($seconds / 60) % 60;
        $s = $seconds % 60;
        $monthlyTime = sprintf("%02d:%02d:%02d", $H, $i, $s);

        return $monthlyTime;
    }

    //Query Scopes:

    public function scopeEnrolled($query)
    {

        return $query->where('ccm_status', 'enrolled');
    }

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
        array $days,
        $fromTime,
        $toTime
    ) {
        $daysNumber = [
            1,
            2,
            3,
            4,
            5,
        ];

        if (!empty($days)) {
            $daysNumber = $days;
        }

        $timeFrom = '09:00:00';
        $timeTo = '17:00:00';

        if (!empty($fromTime)) {
            $timeFrom = Carbon::parse($fromTime)->format('H:i:s');
        }
        if (!empty($toTime)) {
            $timeTo = Carbon::parse($toTime)->format('H:i:s');
        }

        return PatientContactWindow::sync(
            $this,
            $daysNumber,
            $timeFrom,
            $timeTo
        );
    }

    public function scopeHasFamily($query)
    {

        return $query->whereNotNull('family_id');
    }

    public function lastReachedNurse()
    {

        return Call::where('inbound_cpm_id', $this->user_id)
                   ->whereNotNull('called_date')
                   ->orderBy('called_date', 'desc')
                   ->first()['outbound_cpm_id'];
    }

    public function lastNurseThatPerformedActivity()
    {

        $id = Activity::where('patient_id', $this->user_id)
                  ->whereHas('provider', function ($q) {
                      $q->whereHas('roles', function ($k) {
                          $k->where('name', 'care-center');
                      });
                  })
                  ->orderBy('created_at', 'desc')
                  ->first()['provider_id'];

        return Nurse::where('user_id', $id)->first();
    }


    /**
     * Returns nurseInfos that have:
     *  - a call window in the future
     *  - location intersection with the patient's preferred contact location
     *
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
//                dd();

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

    public function isCCMComplex()
    {
        return $this->monthlySummaries
                ->where('month_year', Carbon::now()->firstOfMonth())
                ->first()
                ->is_ccm_complex ?? false;
    }
}

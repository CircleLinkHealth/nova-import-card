<?php namespace App;

use App\Contracts\Serviceable;
use App\Facades\StringManipulation;
use App\Importer\Models\ImportedItems\DemographicsImport;
use App\Models\CCD\Allergy;
use App\Models\CCD\CcdInsurancePolicy;
use App\Models\CCD\Medication;
use App\Models\CCD\Problem;
use App\Models\CPM\Biometrics\CpmBloodPressure;
use App\Models\CPM\Biometrics\CpmBloodSugar;
use App\Models\CPM\Biometrics\CpmSmoking;
use App\Models\CPM\Biometrics\CpmWeight;
use App\Models\CPM\CpmBiometric;
use App\Models\CPM\CpmLifestyle;
use App\Models\CPM\CpmMedicationGroup;
use App\Models\CPM\CpmMisc;
use App\Models\CPM\CpmProblem;
use App\Models\CPM\CpmSymptom;
use App\Models\EmailSettings;
use App\Models\MedicalRecords\Ccda;
use App\Notifications\ResetPassword;
use App\Services\UserService;
use App\Traits\HasEmrDirectAddress;
use Carbon\Carbon;
use DateTime;
use Faker\Factory;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\QueryException;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Zizaco\Entrust\Traits\EntrustUserTrait;


class User extends Model implements AuthenticatableContract, CanResetPasswordContract, Serviceable
{
    const FORWARD_ALERTS_IN_ADDITION_TO_PROVIDER = 'forward_alerts_in_addition_to_provider';
    const FORWARD_ALERTS_INSTEAD_OF_PROVIDER = 'forward_alerts_instead_of_provider';

    const FORWARD_CAREPLAN_APPROVAL_EMAILS_IN_ADDITION_TO_PROVIDER = 'forward_careplan_approval_emails_in_addition_to_provider';
    const FORWARD_CAREPLAN_APPROVAL_EMAILS_INSTEAD_OF_PROVIDER = 'forward_careplan_approval_emails_instead_of_provider';

    use Authenticatable,
        CanResetPassword,
        HasEmrDirectAddress,
        Notifiable,
        SoftDeletes;

    use EntrustUserTrait {
        EntrustUserTrait::restore insteadof SoftDeletes;
    }

    use \Venturecraft\Revisionable\RevisionableTrait;
    public $rules = [
        'username'         => 'required',
        'email'            => 'required|email',
        'password'         => 'required|min:8',
        'password_confirm' => 'required|same:password',
    ];
    public $patient_rules = [
        "daily_reminder_optin"    => "required",
        "daily_reminder_time"     => "required",
        "daily_reminder_areas"    => "required",
        "hospital_reminder_optin" => "required",
        "hospital_reminder_time"  => "required",
        "hospital_reminder_areas" => "required",
        "first_name"              => "required",
        "last_name"               => "required",
        "gender"                  => "required",
        "mrn_number"              => "required",
        "birth_date"              => "required",
        "home_phone_number"       => "required",
        "consent_date"            => "required",
        "ccm_status"              => "required",
        "program_id"              => "required",
    ];
    protected $attributes = [
        'timezone' => 'America/New_York',
    ];
    protected $revisionCreationsEnabled = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'password',
        'email',
        'user_url',
        'user_registered',
        'user_activation_log',
        'user_status',
        'auto_attach_programs',
        'display_name',
        'spam',
        'first_name',
        'last_name',
        'address',
        'address2',
        'city',
        'state',
        'zip',
        'timezone',
        'is_auto_generated',
        'program_id',
        'remember_token',
        'last_login',
        'is_online',
    ];

    protected $hidden = [
//        'password',
    ];

    protected $dates = ['user_registered'];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($user) {

        });

        self::saved(function ($user) {

//            $user->load('roles');

        });

        static::deleting(function ($user) {
            $user->providerInfo()->delete();
            $user->patientInfo()->delete();
            $user->carePlan()->delete();
            $user->careTeamMembers()->delete();
            $user->inboundCalls()->delete();
        });

        self::restoring(function ($user) {
            $user->providerInfo()->restore();
            $user->patientInfo()->restore();
            $user->carePlan()->restore();
            $user->careTeamMembers()->restore();
        });
    }

    public function getEmailForPasswordReset()
    {
        return $this->email;
    }


    /*
     *
     * CCD Models
     *
     */

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function ccdAllergies()
    {
        return $this->hasMany(Allergy::class, 'patient_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function ccdInsurancePolicies()
    {
        return $this->hasMany(CcdInsurancePolicy::class, 'patient_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ccdMedications()
    {
        return $this->hasMany(Medication::class, 'patient_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function ccdProblems()
    {
        return $this->hasMany(Problem::class, 'patient_id');
    }

    public function careAmbassador()
    {

        return $this->hasOne(CareAmbassador::class);

    }

    /*****/

    /*
     *
     * CPM Models
     *
     */

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmBiometrics()
    {
        return $this->belongsToMany(CpmBiometric::class, 'cpm_biometrics_users', 'patient_id')
            ->withPivot('cpm_instruction_id')
            ->withTimestamps('created_at', 'updated_at');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmLifestyles()
    {
        return $this->belongsToMany(CpmLifestyle::class, 'cpm_lifestyles_users', 'patient_id')
            ->withPivot('cpm_instruction_id')
            ->withTimestamps('created_at', 'updated_at');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmMedicationGroups()
    {
        return $this->belongsToMany(CpmMedicationGroup::class, 'cpm_medication_groups_users', 'patient_id')
            ->withPivot('cpm_instruction_id')
            ->withTimestamps('created_at', 'updated_at');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmMiscs()
    {
        return $this->belongsToMany(CpmMisc::class, 'cpm_miscs_users', 'patient_id')
            ->withPivot('cpm_instruction_id')
            ->withTimestamps('created_at', 'updated_at');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmProblems()
    {
        return $this->belongsToMany(CpmProblem::class, 'cpm_problems_users', 'patient_id')
            ->withPivot('cpm_instruction_id')
            ->withTimestamps('created_at', 'updated_at');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmSymptoms()
    {
        return $this->belongsToMany(CpmSymptom::class, 'cpm_symptoms_users', 'patient_id')
            ->withPivot('cpm_instruction_id')
            ->withTimestamps('created_at', 'updated_at');
    }


    /*
     *
     * CPM Biometrics
     *
     */

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cpmBloodPressure()
    {
        return $this->hasOne(CpmBloodPressure::class, 'patient_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cpmBloodSugar()
    {
        return $this->hasOne(CpmBloodSugar::class, 'patient_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cpmSmoking()
    {
        return $this->hasOne(CpmSmoking::class, 'patient_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cpmWeight()
    {
        return $this->hasOne(CpmWeight::class, 'patient_id');
    }

    /*****/

    public function foreignId()
    {
        return $this->hasMany(ForeignId::class);
    }

    public function patientDemographics()
    {
        return $this->hasMany(DemographicsImport::class, 'provider_id');
    }

    public function comment()
    {
        return $this->hasMany('App\Comment', 'user_id', 'id');
    }

    public function observations()
    {
        return $this->hasMany('App\Observation', 'user_id', 'id');
    }

    public function careItems()
    {
        return $this->belongsToMany('App\CareItem', 'care_item_user_values', 'user_id',
            'care_item_id')->withPivot('value');
    }

    public function activities()
    {
        return $this->hasMany('App\Activity');
    }

    public function notes()
    {
        return $this->hasMany('App\Note', 'patient_id', 'id');
    }

    public function patientActivities()
    {
        return $this->hasMany('App\Activity', 'patient_id', 'id');
    }

    public function providerInfo()
    {
        return $this->hasOne('App\ProviderInfo', 'user_id', 'id');
    }

    public function nurseInfo()
    {
        return $this->hasOne(Nurse::class, 'user_id', 'id');
    }

    public function carePlan()
    {
        return $this->hasOne(CarePlan::class, 'user_id', 'id');
    }

    public function inboundCalls()
    {
        return $this->hasMany('App\Call', 'inbound_cpm_id', 'id');
    }

    public function outboundCalls()
    {
        return $this->hasMany('App\Call', 'outbound_cpm_id', 'id');
    }

    public function inboundMessages()
    {
        return $this->hasMany(Message::class, 'receiver_cpm_id', 'id');
    }

    public function outboundMessages()
    {
        return $this->hasMany(Message::class, 'sender_cpm_id', 'id');
    }

    /**
     * @return array
     */
    public function viewablePatientIds(): array
    {
        return User::ofType('participant')
            ->whereHas('practices', function ($q) {
                $q->whereIn('program_id', $this->viewableProgramIds());
            })
            ->pluck('id')
            ->all();
    }

    public function viewableProgramIds(): array
    {
        return $this->practices
            ->pluck('id')
            ->all();
    }

    public function viewableProviderIds()
    {
        // get all patients who are in the same programs
        $programIds = $this->viewableProgramIds();
        $patientIds = User::whereHas('practices', function ($q) use (
            $programIds
        ) {
            $q->whereIn('program_id', $programIds);
        });

        //if(!Auth::user()->can('admin-access')) {
        $patientIds->whereHas('roles', function ($q) {
            $q->where('name', '=', 'provider');
        });
        //}

        $patientIds = $patientIds->pluck('id')->all();

        return $patientIds;
    }

    public function viewableUserIds()
    {
        // get all patients who are in the same programs
        $programIds = $this->viewableProgramIds();
        $patientIds = User::whereHas('practices', function ($q) use (
            $programIds
        ) {
            $q->whereIn('program_id', $programIds);
        });

        $patientIds = $patientIds->pluck('id')->all();

        return $patientIds;
    }

    public function userMeta($key = null)
    {
        $userMeta = $this->meta->pluck('meta_value', 'meta_key')->all();
        $userMeta['user_config'] = $this->userConfig();
        if (!$userMeta) {
            return false;
        } else {
            return $userMeta;
        }
    }

    public function userConfig()
    {
        $key = 'wp_' . $this->primaryProgramId() . '_user_config';
        $userConfig = $this->meta->where('meta_key', $key)->first();
        if (!$userConfig) {
            return false;
        } else {
            return unserialize($userConfig['meta_value']);
        }
    }


    // END RELATIONSHIPS

    public function primaryProgramId()
    {
        return $this->program_id;
    }

    public function getPrimaryPracticeIdAttribute()
    {
        return $this->program_id;
    }

    public function getUserMetaByKey($key)
    {
        $value = '';
        $meta = $this->meta->where('meta_key', $key)->first();
        if (!empty($meta && $meta->meta_value != '')) {
            $value = $meta->meta_value;
        }

        return $value;
    }

    public function setUserMetaByKey(
        $key,
        $value
    ) {
        $meta = $this->meta->where('meta_key', $key)->first();
        if (!empty($meta)) {
            $meta->meta_value = $value;
            $meta->save();
        } else {
            $meta = new UserMeta;
            $meta->meta_key = $key;
            $meta->meta_value = $value;
            $meta->user_id = $this->id;
            $this->meta()->save($meta);
            $this->load('meta');
        }

        return true;
    }

    public function meta()
    {
        return $this->hasMany('App\UserMeta', 'user_id', 'id');
    }

    public function primaryProgramName()

    {
        return Practice::find($this->primaryProgramId())->display_name;
    }

    public function getUserConfigByKey($key)
    {
        $userConfig = $this->userConfig();

        return (isset($userConfig[$key]))
            ? $userConfig[$key]
            : '';
    }

    public function setUserConfigByKey(
        $key,
        $value
    ) {
        $configKey = 'wp_' . $this->primaryProgramId() . '_user_config';
        $userConfig = UserMeta::where('user_id', $this->id)->where('meta_key', $configKey)->first();
        if (empty($userConfig)) {
            $userConfig = new UserMeta;
            $userConfig->meta_key = $configKey;
            $userConfig->meta_value = serialize([]);
            $userConfig->user_id = $this->id;
            $userConfig->save();
            $userConfigArray = [];
        } else {
            $userConfigArray = unserialize($userConfig['meta_value']);
        }

        // serialize value if needed
        /*
        if(is_array($value)) {
            $value = serialize($value);
        }
        */
        $userConfigArray[$key] = $value;
        $userConfig->meta_value = serialize($userConfigArray);
        $userConfig->save();

        return true;
    }

    public function setUserAttributeByKey(
        $key,
        $value
    ) {
        $func = create_function('$c', 'return strtoupper($c[1]);');
        $attribute = preg_replace_callback('/_([a-z])/', $func, $key);

        // these are now on User model, no longer remote attributes:
        if ($key === 'firstName' || $key == 'lastName') {
            return true;
        }

        // hack overrides and depreciated keys, @todo fix these
        if ($attribute == 'careplanProviderDate') {
            $attribute = 'careplanProviderApproverDate';
        } else {
            if ($attribute == 'mrnNumber') {
                $attribute = 'mrn';
            } else {
                if ($attribute == 'studyPhoneNumber') {
                    $attribute = 'phone';
                } else {
                    if ($attribute == 'billingProvider') {
                        $attribute = 'billingProviderID';
                    } else {
                        if ($attribute == 'leadContact') {
                            $attribute = 'leadContactID';
                        } else {
                            if ($attribute == 'programId') {
                                return false;
                            }
                        }
                    }
                }
            }
        }

        // serialize any arrays
        if (is_array($value)) {
            $value = serialize($value);
        }

        // get before for debug
        $before = $this->$attribute;
        if (is_array($before)) {
            $before = serialize($before);
        }

        // call save attribute
        $this->$attribute = $value;
        $this->save();

        // get after for debug
        $after = $this->$attribute;
        if (is_array($after)) {
            $after = serialize($after);
        }

        return true;
    }

    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = ucwords($value);
        $this->display_name = $this->fullName;

        return true;
    }

    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = $value;
        $this->display_name = $this->fullName;

        return true;
    }

    public function getFullNameAttribute()
    {
        $firstName = ucwords($this->first_name);
        $lastName = ucwords($this->last_name);

        return $firstName . ' ' . $lastName;
    }

    public function getFullNameWithIdAttribute()
    {
        $name = $this->fullName;

        return $name . ' (' . $this->id . ')';
    }

    public function getPreferredCcContactDaysAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->preferred_cc_contact_days;
    }

    public function setPreferredCcContactDaysAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->preferred_cc_contact_days = $value;
        $this->patientInfo->save();

        return true;
    }

    public function getActiveDateAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->active_date;
    }

    public function setActiveDateAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->active_date = $value;
        $this->patientInfo->save();

        return true;
    }

    public function getRegistrationDateAttribute()
    {
        return $this->user_registered;
    }

    public function setRegistrationDateAttribute($value)
    {
        $this->user_registered = $value;
        $this->save();

        return true;
    }

    public function getSpecialtyAttribute()
    {
        if (!$this->providerInfo) {
            return '';
        }

        return $this->providerInfo->specialty;
    }

    public function setSpecialtyAttribute($value)
    {
        if (!$this->providerInfo) {
            return '';
        }
        $this->providerInfo->specialty = $value;
        $this->providerInfo->save();
    }

    public function getNpiNumberAttribute()
    {
        if (!$this->providerInfo) {
            return '';
        }

        return $this->providerInfo->npi_number;
    }

    public function setNpiNumberAttribute($value)
    {
        if (!$this->providerInfo) {
            return '';
        }
        $this->providerInfo->npi_number = $value;
        $this->providerInfo->save();
    }

    public function getQualificationAttribute()
    {
        if (!$this->providerInfo) {
            return '';
        }

        return $this->providerInfo->qualification;
    }

    public function setQualificationAttribute($value)
    {
        if (!$this->providerInfo) {
            return '';
        }
        $this->providerInfo->qualification = $value;
        $this->providerInfo->save();
    }

    public function getDailyReminderOptinAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->daily_reminder_optin;
    }

    public function setDailyReminderOptinAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->daily_reminder_optin = $value;
        $this->patientInfo->save();

        return true;
    }

    public function getDailyReminderTimeAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->daily_reminder_time;
    }

    public function setDailyReminderTimeAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->daily_reminder_time = $value;
        $this->patientInfo->save();

        return true;
    }

    public function getDailyReminderAreasAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->daily_reminder_areas;
    }

    public function setDailyReminderAreasAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->daily_reminder_areas = $value;
        $this->patientInfo->save();

        return true;
    }

    public function getHospitalReminderOptinAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->hospital_reminder_optin;
    }

    public function setHospitalReminderOptinAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->hospital_reminder_optin = $value;
        $this->patientInfo->save();

        return true;
    }

    public function getHospitalReminderTimeAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->hospital_reminder_time;
    }

    public function setHospitalReminderTimeAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->hospital_reminder_time = $value;
        $this->patientInfo->save();

        return true;
    }

    public function getHospitalReminderAreasAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->hospital_reminder_areas;
    }

    public function setHospitalReminderAreasAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->hospital_reminder_areas = $value;
        $this->patientInfo->save();

        return true;
    }

    public function getPrimaryPhoneAttribute()
    {
        if (!$this->phoneNumbers) {
            return '';
        }
        $phoneNumber = $this->phoneNumbers->where('is_primary', 1)->first();
        if ($phoneNumber) {
            return $phoneNumber->number;
        } else {
            return '';
        }
    }

    /**
     * Delete all existing Phone Numbers and replace them with a new primary number.
     *
     * @param $number
     * @param $type
     * @param bool $isPrimary
     *
     * @return bool
     */
    public function clearAllPhonesAndAddNewPrimary(
        $number,
        $type,
        $isPrimary = false,
        $extension = null
    ) {
        $this->phoneNumbers()->delete();

        if (empty($number)) {
            //assume we wanted to delete the phone(s)
            return true;
        }

        return $this->phoneNumbers()->create([
            'number'     => StringManipulation::formatPhoneNumber($number),
            'type'       => PhoneNumber::getTypes()[$type],
            'is_primary' => $isPrimary,
            'extension'  => $extension,
        ]);
    }

    public function phoneNumbers()
    {
        return $this->hasMany(PhoneNumber::class, 'user_id', 'id');
    }

    public function getHomePhoneNumberAttribute()
    {
        return $this->getPhoneAttribute();
    }

    public function getPhoneAttribute()
    {
        if (!$this->phoneNumbers) {
            return '';
        }

        $phoneNumbers = $this->phoneNumbers;

        if (count($phoneNumbers) == 1) {
            return $phoneNumbers->first()->number;
        }

        $primary = $phoneNumbers->where('is_primary', true)->first();
        if ($primary) {
            return $primary->number;
        }

        if (count($phoneNumbers) > 0) {
            return $phoneNumbers->first()->number;
        }

        return '';
    }

    public function setHomePhoneNumberAttribute($value)
    {
        return $this->setPhoneAttribute($value);
    }

    public function setPhoneAttribute($value)
    {
        if (!$this->phoneNumbers) {
            return '';
        }
        $phoneNumber = $this->phoneNumbers->where('type', 'home')->first();
        if ($phoneNumber) {
            $phoneNumber->number = $value;
        } else {
            $phoneNumber = new PhoneNumber();
            $phoneNumber->user_id = $this->id;
            $phoneNumber->is_primary = 1;
            $phoneNumber->number = $value;
            $phoneNumber->type = 'home';
        }
        $phoneNumber->save();

        return true;
    }

    public function getWorkPhoneNumberAttribute()
    {
        if (!$this->phoneNumbers) {
            return '';
        }
        $phoneNumber = $this->phoneNumbers->where('type', 'work')->first();
        if ($phoneNumber) {
            return $phoneNumber->number;
        } else {
            return '';
        }
    }

    public function setWorkPhoneNumberAttribute($value)
    {
        if (!$this->phoneNumbers) {
            return '';
        }
        $phoneNumber = $this->phoneNumbers->where('type', 'work')->first();
        if ($phoneNumber) {
            $phoneNumber->number = $value;
        } else {
            $phoneNumber = new PhoneNumber();
            $phoneNumber->user_id = $this->id;
            $phoneNumber->number = $value;
            $phoneNumber->type = 'work';
        }
        $phoneNumber->save();

        return true;
    }

    public function getMobilePhoneNumberAttribute()
    {
        if (!$this->phoneNumbers) {
            return '';
        }
        $phoneNumber = $this->phoneNumbers->where('type', 'mobile')->first();
        if ($phoneNumber) {
            return $phoneNumber->number;
        } else {
            return '';
        }
    }

    public function setMobilePhoneNumberAttribute($value)
    {
        if (!$this->phoneNumbers) {
            return '';
        }
        $phoneNumber = $this->phoneNumbers->where('type', 'mobile')->first();
        if ($phoneNumber) {
            $phoneNumber->number = $value;
        } else {
            $phoneNumber = new PhoneNumber();
            $phoneNumber->user_id = $this->id;
            $phoneNumber->number = $value;
            $phoneNumber->type = 'mobile';
        }
        $phoneNumber->save();

        return true;
    }

    public function getBirthDateAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->birth_date;
    }

    public function setBirthDateAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->birth_date = $value;
        $this->patientInfo->save();

        return true;
    }

    public function getGenderAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->gender;
    }

    public function setGenderAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->gender = $value;
        $this->patientInfo->save();

        return true;
    }

    public function setEmailAddressAttribute($value)
    {
        return $this->email = $value;
    }

    public function getAgeAttribute()
    {
        $from = new DateTime($this->birthDate);
        $to = new DateTime('today');

        return $from->diff($to)->y;
    }

    public function getCurMonthActivityTimeAttribute()
    {
        return $this->patientInfo->cur_month_activity_time;
    }

    public function setCurMonthActivityTimeAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->cur_month_activity_time = $value;
        $this->patientInfo->save();
    }

    public function getPreferredContactTimeAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->preferred_contact_time;
    }

    public function setPreferredContactTimeAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->preferred_contact_time = $value;
        $this->patientInfo->save();

        return true;
    }

    public function getPreferredContactMethodAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->preferred_contact_method;
    }

    public function setPreferredContactMethodAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->preferred_contact_method = $value;
        $this->patientInfo->save();

        return true;
    }

    public function getPreferredContactLanguageAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->preferred_contact_language;
    }

    public function setPreferredContactLanguageAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->preferred_contact_language = $value;
        $this->patientInfo->save();

        return true;
    }

    public function getMrnNumberAttribute()
    {
        return $this->getMRNAttribute();
    }

    public function getMRNAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->mrn_number;
    }

    public function setMrnNumberAttribute($value)
    {
        return $this->setMRNAttribute($value);
    }

    public function setMRNAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->mrn_number = $value;
        $this->patientInfo->save();

        return true;
    }

    public function getCareTeamAttribute()
    {
        $ct = [];
        $careTeamMembers = $this->careTeamMembers->where('type', 'member');
        if ($careTeamMembers->count() > 0) {
            foreach ($careTeamMembers as $careTeamMember) {
                $ct[] = $careTeamMember->member_user_id;
            }
        }

        return $ct;
    }

    public function setCareTeamAttribute(array $memberUserIds)
    {
        if (!is_array($memberUserIds)) {
            $this->careTeamMembers()->where('type', 'member')->delete();

            return false; // must be array
        }
        $this->careTeamMembers()->where('type', 'member')->whereNotIn('member_user_id',
            $memberUserIds)->delete();
        foreach ($memberUserIds as $memberUserId) {
            $careTeamMember = $this->careTeamMembers()->where('type', 'member')->where('member_user_id',
                $memberUserId)->first();
            if ($careTeamMember) {
                $careTeamMember->member_user_id = $memberUserId;
            } else {
                $careTeamMember = new CarePerson();
                $careTeamMember->user_id = $this->id;
                $careTeamMember->member_user_id = $memberUserId;
                $careTeamMember->type = 'member';
            }
            $careTeamMember->save();
        }

        return true;
    }

    public function careTeamMembers()
    {
        return $this->hasMany(CarePerson::class, 'user_id', 'id');
    }

    /**
     * Get the CarePeople who have subscribed to receive alerts for this Patient.
     * Returns a Collection of User objects, or an Empty Collection.
     *
     * @return Collection
     */
    public function getCareTeamReceivesAlertsAttribute()
    {
        if (!$this->primaryPractice->send_alerts) {
            return new Collection();
        }

        $careTeam = $this->careTeamMembers->where('alert', '=', true)
            ->keyBy('member_user_id')
            ->unique()
            ->values();

        $users = new Collection();

        foreach ($careTeam as $carePerson) {
            if ($carePerson->user->forwardAlertsTo->isEmpty() && $carePerson->user) {
                $users->push($carePerson->user);
            }

            foreach ($carePerson->user->forwardAlertsTo as $forwardee) {
                if ($forwardee->pivot->name == User::FORWARD_ALERTS_IN_ADDITION_TO_PROVIDER) {
                    $users->push($carePerson->user);
                    $users->push($forwardee);
                }

                if ($forwardee->pivot->name == User::FORWARD_ALERTS_INSTEAD_OF_PROVIDER) {
                    $users->push($forwardee);
                }
            }
        }

        foreach ($this->locations as $location) {
            if (!$location->clinicalEmergencyContact->isEmpty()) {
                $contact = $location->clinicalEmergencyContact->first();

                if ($contact->pivot->name == CarePerson::INSTEAD_OF_BILLING_PROVIDER) {
                    $users = new Collection();
                    $users->push($contact);

                    return $users;
                }

                $users->push($contact);
            }
        }

        return $users;
    }

    public function getSendAlertToAttribute()
    {
        $ctmsa = [];
        if (!$this->careTeamMembers) {
            return '';
        }
        if ($this->careTeamMembers->count() > 0) {
            foreach ($this->careTeamMembers as $careTeamMember) {
                if ($careTeamMember->alert) {
                    $ctmsa[] = $careTeamMember->member_user_id;
                }
            }
        }

        return $ctmsa;
    }

    public function setSendAlertToAttribute($memberUserIds)
    {
        if (!is_array($memberUserIds)) {
            $this->careTeamMembers()->where('alert', '=', true)->delete();

            return false; // must be array
        }
        $this->careTeamMembers()->where('alert', '=', true)->whereNotIn('member_user_id',
            $memberUserIds)->delete();
        foreach ($memberUserIds as $memberUserId) {
            $careTeamMember = $this->careTeamMembers()->where('alert', '=', false)
                ->where('member_user_id', $memberUserId)
                ->first();
            if ($careTeamMember) {
                $careTeamMember->alert = true;
                $careTeamMember->save();
            }
        }

        return true;
    }

    public function getBillingProviderIDAttribute()
    {
        $bp = '';
        if (!$this->careTeamMembers) {
            return '';
        }
        if ($this->careTeamMembers->count() > 0) {
            foreach ($this->careTeamMembers as $careTeamMember) {
                if ($careTeamMember->type == 'billing_provider') {
                    $bp = $careTeamMember->member_user_id;
                }
            }
        }

        return $bp;
    }


    public function setBillingProviderIDAttribute($value)
    {
        if (empty($value)) {
            $this->careTeamMembers()->where('type', 'billing_provider')->delete();

            return true;
        }
        $careTeamMember = $this->careTeamMembers()->where('type', 'billing_provider')->first();
        if ($careTeamMember) {
            $careTeamMember->member_user_id = $value;
        } else {
            $careTeamMember = new CarePerson();
            $careTeamMember->user_id = $this->id;
            $careTeamMember->member_user_id = $value;
            $careTeamMember->type = 'billing_provider';
        }
        $careTeamMember->save();

        return true;
    }

    public function getLeadContactIDAttribute()
    {
        $lc = [];
        if (!$this->careTeamMembers) {
            return '';
        }
        if ($this->careTeamMembers->count() > 0) {
            foreach ($this->careTeamMembers as $careTeamMember) {
                if ($careTeamMember->type == 'lead_contact') {
                    $lc = $careTeamMember->member_user_id;
                }
            }
        }

        return $lc;
    }

    public function setLeadContactIDAttribute($value)
    {
        if (empty($value)) {
            $this->careTeamMembers()->where('type', 'lead_contact')->delete();

            return true;
        }
        $careTeamMember = $this->careTeamMembers()->where('type', 'lead_contact')->first();
        if ($careTeamMember) {
            $careTeamMember->member_user_id = $value;
        } else {
            $careTeamMember = new CarePerson();
            $careTeamMember->user_id = $this->id;
            $careTeamMember->member_user_id = $value;
            $careTeamMember->type = 'lead_contact';
        }
        $careTeamMember->save();

        return true;
    }

    public function getPreferredLocationAddress()
    {
        if (!$this->patientInfo) {
            return '';
        }
        $locationId = $this->patientInfo->preferred_contact_location;
        if (empty($locationId)) {
            return false;
        }
        $location = Location::find($locationId);

        return $location;
    }

    public function getPreferredLocationName()
    {
        if (!$this->patientInfo) {
            return '';
        }
        $locationId = $this->patientInfo->preferred_contact_location;
        if (empty($locationId)) {
            return false;
        }
        $location = Location::find($locationId);

        return (isset($location->name))
            ?
            $location->name
            :
            '';
    }

    public function getPreferredContactLocationAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->preferred_contact_location;
    }

    public function setPreferredContactLocationAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->preferred_contact_location = $value;
        $this->patientInfo->save();

        return true;
    }

    public function getPrefixAttribute()
    {
        if (!$this->providerInfo) {
            return '';
        }

        return $this->providerInfo->prefix;
    }

    public function setPrefixAttribute($value)
    {
        if (!$this->providerInfo) {
            return '';
        }
        $this->providerInfo->prefix = $value;
        $this->providerInfo->save();
    }

    public function getConsentDateAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->consent_date;
    }

    public function setConsentDateAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->consent_date = $value;
        $this->patientInfo->save();

        return true;
    }

    public function getAgentNameAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->agent_name;
    }

    public function setAgentNameAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->agent_name = $value;
        $this->patientInfo->save();

        return true;
    }

    public function getAgentTelephoneAttribute()
    {
        return $this->getAgentPhoneAttribute();
    }

    public function getAgentPhoneAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->agent_telephone;
    }

    public function setAgentTelephoneAttribute($value)
    {
        return $this->setAgentPhoneAttribute($value);
    }

    public function setAgentPhoneAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->agent_telephone = $value;
        $this->patientInfo->save();

        return true;
    }

    public function getAgentEmailAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->agent_email;
    }

    public function setAgentEmailAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->agent_email = $value;
        $this->patientInfo->save();

        return true;
    }

    public function getAgentRelationshipAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->agent_relationship;
    }

    public function setAgentRelationshipAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->agent_relationship = $value;
        $this->patientInfo->save();

        return true;
    }

    public function getCarePlanQAApproverAttribute()
    {
        if (!$this->carePlan) {
            return '';
        }

        return $this->carePlan->qa_approver_id;
    }

    public function setCarePlanQAApproverAttribute($value)
    {
        if (!$this->carePlan) {
            return '';
        }
        $this->carePlan->qa_approver_id = $value;
        $this->carePlan->save();

        return true;
    }

    public function getCarePlanQADateAttribute()
    {
        if (!$this->carePlan) {
            return '';
        }

        return $this->carePlan->qa_date;
    }

    public function setCarePlanQADateAttribute($value)
    {
        if (!$this->carePlan) {
            return '';
        }
        $this->carePlan->qa_date = $value;
        $this->carePlan->save();

        return true;
    }

    public function getCarePlanProviderApproverAttribute()
    {
        if (!$this->carePlan) {
            return '';
        }

        return $this->carePlan->provider_approver_id;
    }

    public function setCarePlanProviderApproverAttribute($value)
    {
        if (!$this->carePlan) {
            return '';
        }
        $this->carePlan->provider_approver_id = $value;
        $this->carePlan->save();

        return true;
    }

    public function getCarePlanProviderApproverDateAttribute()
    {
        if (!$this->carePlan) {
            return '';
        }

        return $this->carePlan->provider_date;
    }

    public function setCarePlanProviderApproverDateAttribute($value)
    {
        if (!$this->carePlan) {
            return '';
        }
        $this->carePlan->provider_date = $value;
        $this->carePlan->save();

        return true;
    }

    public function getCarePlanStatusAttribute()
    {
        if (!$this->carePlan) {
            return '';
        }

        return $this->carePlan->status;
    }

    public function setCarePlanStatusAttribute($value)
    {
        if (!$this->carePlan) {
            return '';
        }
        $this->carePlan->status = $value;
        $this->carePlan->save();

        return true;
    }

    public function getCareplanLastPrintedAttribute()
    {
        if (!$this->carePlan) {
            return '';
        }

        return $this->carePlan->last_printed;
    }

    public function setCareplanLastPrintedAttribute($value)
    {
        if (!$this->carePlan) {
            return '';
        }
        $this->carePlan->last_printed = $value;
        $this->carePlan->save();

        return true;
    }

    public function getCcmStatusAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->ccm_status;
    }

    public function setCcmStatusAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $statusBefore = $this->patientInfo->ccm_status;
        $this->patientInfo->ccm_status = $value;
        $this->patientInfo->save();
        // update date tracking
        if ($statusBefore !== $value) {
            if ($value == 'paused') {
                $this->datePaused = date("Y-m-d H:i:s");
            };
            if ($value == 'withdrawn') {
                $this->dateWithdrawn = date("Y-m-d H:i:s");
            };
        }

        return true;
    }

    public function getDatePausedAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->date_paused;
    }

    public function setDatePausedAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->date_paused = $value;
        $this->patientInfo->save();

        return true;
    }

    public function getDateWithdrawnAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->date_withdrawn;
    }

    public function setDateWithdrawnAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->date_withdrawn = $value;
        $this->patientInfo->save();

        return true;
    }

    public function primaryPractice()
    {
        return $this->belongsTo(Practice::class, 'program_id', 'id');
    }

    public function scramble()
    {
        $faker = Factory::create();
        if (!$faker) {
            return false;
        }

        //dd($randomUserInfo);
        // set random data
        $user = $this;
        $user->first_name = $faker->firstName;
        $user->last_name = 'Z-' . $faker->lastName;
        $user->username = $faker->userName;
        $user->password = $faker->password;
        $user->email = $faker->freeEmail;
        $user->MRN = rand();
        $user->gender = 'M';
        $user->address = $faker->address;
        $user->address2 = $faker->secondaryAddress;
        $user->city = $faker->city;
        $user->state = $faker->stateAbbr;
        $user->zip = $faker->postcode;
        $user->phone = '111-234-5678';
        $user->workPhoneNumber = '222-234-5678';
        $user->mobilePhoneNumber = '333-234-5678';
        $user->birthDate = $faker->dateTimeThisCentury->format('Y-m-d');
        $user->agentName = 'Secret Agent';
        $user->agentPhone = '111-234-5678';
        $user->agentEmail = 'secret@agent.net';
        $user->agentRelationship = 'SA';
        $user->save();

    }


// MISC, these should be removed eventually

    public function createNewUser(
        $email,
        $password
    ) {
        $this->username = $email;
        $this->email = $email;
        $this->password = bcrypt($password);
        $this->save();

        return $this;
    }

    public function getUCP()
    {
        $userUcp = $this->ucp()->with([
            'item.meta',
            'item.question',
        ])->get();
        $userUcpData = [
            'ucp'        => [],
            'obs_keys'   => [],
            'alert_keys' => [],
        ];
        if ($userUcp->count() > 0) {
            foreach ($userUcp as $userUcpItem) {
                $userUcpData['ucp'][] = $userUcpItem;
                if (isset($userUcpItem->item->question)) {
                    $question = $userUcpItem->item->question;
                    if ($question) {
                        // obs key should be unique
                        $userUcpData['obs_keys'][$question->obs_key] = $userUcpItem->meta_value;
                    }
                }

                if (isset($userUcpItem->item->meta)) {
                    $alert_key = $userUcpItem->item->meta()->where('meta_key', '=', 'alert_key')->first();
                    if ($alert_key) {
                        // alert_key should be unique
                        $userUcpData['alert_keys'][$alert_key->meta_value] = $userUcpItem->meta_value;
                    }
                }
            }
            $userUcpData['ucp'] = collect($userUcpData['ucp']);
        }

        return $userUcpData;
    }

    public function ucp()
    {
        return $this->hasMany('App\CPRulesUCP', 'user_id', 'id');
    }

    public function service()
    {
        return new UserService();
    }

// user data scrambler

    public function emailSettings()
    {
        return $this->hasOne(EmailSettings::class);
    }

    public function isCCMCountable()
    {

        return (in_array($this->roles[0]->name, Role::CCM_TIME_ROLES));

    }

    /**
     * Scope a query to only include users of a given type (Role).
     *
     * @param $query
     * @param $type
     */
    public function scopeOfType(
        $query,
        $type
    ) {
        $query->whereHas('roles', function ($q) use (
            $type
        ) {
            if (is_array($type)) {
                $q->whereIn('name', $type);
            } else {
                $q->where('name', '=', $type);
            }
        });
    }

    /**
     * Scope a query to include users NOT of a given type (Role).
     *
     * @param $query
     * @param $type
     */
    public function scopeExceptType(
        $query,
        $type
    ) {
        $query->whereHas('roles', function ($q) use (
            $type
        ) {
            if (is_array($type)) {
                $q->whereNotIn('name', $type);
            } else {
                $q->where('name', '!=', $type);
            }
        });
    }

    /**
     * Scope a query to intersect locations with the given user.
     *
     * @param $query
     * @param $user
     */
    public function scopeIntersectLocationsWith(
        $query,
        $user
    ) {
        return $query->whereHas('locations', function ($q) use (
            $user
        ) {
            $q->whereIn('locations.id', $user->locations->pluck('id')->all());
        });
    }

    /**
     * Scope a query to intersect practices with the given user.
     *
     * @param $query
     * @param $user
     */
    public function scopeIntersectPracticesWith(
        $query,
        $user
    ) {
        return $query->whereHas('practices', function ($q) use (
            $user
        ) {
            $q->whereIn('id', $user->viewableProgramIds());
        });
    }

    public function attachPractice(
        $practice,
        bool $grantAdminRights = null,
        bool $subscribeToBillingReports = null,
        $roleId = null
    ) {
        if (is_array($practice)) {
            foreach ($practice as $key => $pract) {
                $this->attachPractice($pract, $grantAdminRights, $subscribeToBillingReports, $roleId);
                unset($key);
            }
        }

        $practice = is_object($practice)
            ? $practice
            : Practice::find($practice);

        $roleId = is_object($roleId)
            ? $roleId->id
            : $roleId;

        try {
            $exists = $this->practice($practice);

            if ($exists) {
                $this->practices()->detach($practice);
            }

            $update = [];

            if (!is_null($grantAdminRights)) {
                $update['has_admin_rights'] = $grantAdminRights;
            }

            if (!is_null($subscribeToBillingReports)) {
                $update['send_billing_reports'] = $subscribeToBillingReports;
            }

            if (!is_null($roleId)) {
                $update['role_id'] = $roleId;
            }

            $attachPractice = $this->practices()->save($practice, $update);
        } catch (\Exception $e) {
            //check if this is a mysql exception for unique key constraint
            if ($e instanceof \Illuminate\Database\QueryException) {
                $errorCode = $e->errorInfo[1];
                \Log::alert($e);
            }
        }

        return true;
    }

    /**
     * Get the specified Practice, if it is related to this User
     * You can pass in a practice_id, practice_slug, or  App\Practice object
     *
     * @param $practice
     *
     * @return mixed
     */
    public function practice($practice)
    {
        if (is_string($practice) && !is_int($practice)) {
            return $this->practices()
                ->where('name', '=', $practice)
                ->first();
        }

        $practiceId = null;

        if (is_object($practice)) {
            $practiceId = $practice->id;
        }

        if (is_int($practice)) {
            $practiceId = $practice;
        }

        if (!$practiceId) {
            return null;
        }

        return $this->practices()
            ->where('program_id', '=', $practiceId)
            ->first();
    }

    public function practices()
    {
        return $this->belongsToMany(Practice::class, 'practice_user', 'user_id', 'program_id')
            ->withPivot('role_id', 'has_admin_rights', 'send_billing_reports');
    }

    /**
     * Attach Location(s)
     *
     * @param $location |array
     */
    public function attachLocation($location)
    {
        if (is_a($location, Collection::class) || is_a($location, EloquentCollection::class)) {
            $id = $location->all();
        }

        if (is_array($location)) {
            foreach ($location as $key => $loc) {
                $this->attachLocation($loc);
                unset($location[$key]);
            }
        }

        if (is_a($location, Location::class)) {
            $id = $location->id;
        }

        try {
            $this->locations()->attach($id);
        } catch (\Exception $e) {
            //check if this is a mysql exception for unique key constraint
            if ($e instanceof \Illuminate\Database\QueryException) {
                $errorCode = $e->errorInfo[1];
                if ($errorCode == 1062) {
                    //do nothing
                    //we don't actually want to terminate the program if we detect duplicates
                    //we just don't wanna add the row again
                }
            }
        }
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class)
            ->withTimestamps();
    }

    /**
     * Get primary practice's name.
     *
     * @return string
     */
    public function getPrimaryPracticeNameAttribute()
    {
        return ucwords($this->primaryPractice->display_name);
    }

    /**
     * Get billing provider's full name.
     *
     * @return string
     */
    public function getBillingProviderNameAttribute()
    {
        $billingProvider = $this->billingProvider();

        return $billingProvider
            ? ucwords($billingProvider->fullName)
            : '';
    }

    /**
     * Get billing provider.
     *
     * @return User
     */
    public function billingProvider(): User
    {
        $billingProvider = $this->careTeamMembers
            ->where('type', 'billing_provider')
            ->first();

        return $billingProvider->user ?? new User();
    }

    public function scopeHasBillingProvider(
        $query,
        $billing_provider_id
    ) {
        return $query->whereHas('careTeamMembers', function ($k) use (
            $billing_provider_id
        ) {
            $k->whereType('billing_provider')
                ->whereMemberUserId($billing_provider_id);
        });
    }


    /**
     * Get billing provider.
     *
     * @return User
     */
    public function leadContact(): User
    {
        $leadContact = $this->careTeamMembers
            ->where('type', 'lead_contact')
            ->first();

        return $leadContact->user ?? new User();
    }

    public function scopeWithCareTeamOfType(
        $query,
        $type
    ) {
        $query->with([
            'careTeamMembers' => function ($q) use (
                $type
            ) {
                $q->where('type', $type)
                    ->with('user');
            },
        ]);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string $token
     *
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    public function latestCcda()
    {
        return $this->ccdas()
            ->orderBy('updated_at', 'desc')
            ->first();
    }

    public function ccdas()
    {
        return $this->hasMany(Ccda::class, 'patient_id', 'id');
    }

    public function getCcmTimeAttribute()
    {
        return $this->patientInfo()->firstOrNew([])->cur_month_activity_time;
    }

    public function patientInfo()
    {
        return $this->hasOne(Patient::class, 'user_id', 'id');
    }

    public function clinicalEmergencyContactLocations()
    {
        return $this->morphedByMany(Location::class, 'contactable', 'contacts')
            ->withPivot('name')
            ->wherePivot('name', '=', 'in_addition_to_billing_provider')
            ->orWherePivot('name', '=', 'instead_of_billing_provider')
            ->withTimestamps();
    }

    public function routeNotificationForTwilio()
    {
        return $this->primaryPhone;
    }

    /**
     * Attach Role to User.
     * Returns false if Role was already attached, and true if it was attached now.
     *
     * @param $roleId
     *
     * @return bool
     */
    public function attachGlobalRole($roleId)
    {
        if (is_array($roleId)) {
            foreach ($roleId as $key => $role) {
                $this->attachGlobalRole($role);
                unset($key);
            }
        }

        if (is_object($roleId)) {
            $roleId = $roleId->id;
        }

        try {
            //Attach the role
            $this->roles()->attach($roleId);
        } catch (\Exception $e) {
            if ($e instanceof QueryException) {
                $errorCode = $e->errorInfo[1];
                if ($errorCode == 1062) {
                    //return false so we know nothing was attached
                    return false;
                }
            }
        }

        return true;
    }

    public function firstOrNewProviderInfo()
    {
        if (!$this->hasRole('provider')) {
            return false;
        }

        return ProviderInfo::firstOrCreate([
            'user_id' => $this->id,
        ]);
    }

    /**
     * Forward Alerts to another User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function forwardAlertsTo()
    {
        return $this->morphToMany(User::class, 'contactable', 'contacts')
            ->withPivot('name')
            ->withTimestamps();
    }

    /**
     * Get the Users that are forwarding alerts to this User.
     * Inverse Relationship of forwardAlertsTo().
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function forwardedAlertsBy()
    {
        return $this->morphedByMany(User::class, 'contactable', 'contacts')
            ->withPivot('name')
            ->wherePivot('name', '=', User::FORWARD_ALERTS_IN_ADDITION_TO_PROVIDER)
            ->orWherePivot('name', '=', User::FORWARD_ALERTS_INSTEAD_OF_PROVIDER)
            ->withTimestamps();
    }

    /**
     * Forward Alerts/Notifications to another User.
     * Attaches forwards to a user using forwardAlertsTo() relationship.
     *
     * @return void
     */
    public function forwardTo($receiverUserId, $forwardTypeName)
    {
        $this->forwardAlertsTo()->attach($receiverUserId, [
            'name' => $forwardTypeName,
        ]);
    }

    /**
     * Get the Users that are forwarding alerts to this User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function forwardedCarePlanApprovalEmailsBy()
    {
        return $this->forwardedAlertsBy()
            ->withPivot('name')
            ->wherePivot('name', '=', User::FORWARD_CAREPLAN_APPROVAL_EMAILS_IN_ADDITION_TO_PROVIDER)
            ->orWherePivot('name', '=', User::FORWARD_CAREPLAN_APPROVAL_EMAILS_INSTEAD_OF_PROVIDER)
            ->withTimestamps();
    }

    /**
     * Get the User's Primary Or Global Role
     *
     * @return Role|null
     */
    public function role()
    {
        if ($this->practice($this->primaryPractice)) {
            $primaryPractice = $this->practice($this->primaryPractice);

            if ($primaryPractice->pivot->role_id) {
                return Role::find($primaryPractice->pivot->role_id);
            }
        }

        return $this->roles->first();
    }

    public function getTimezoneAbbrAttribute() {
        return $this->timezone
            ? Carbon::now($this->timezone)->format('T')
            : false;
    }
}

<?php namespace App;

use App\CLH\CCD\ImportedItems\DemographicsImport;
use App\Contracts\Serviceable;
use App\Models\CCD\CcdAllergy;
use App\Models\CCD\CcdInsurancePolicy;
use App\Models\CCD\CcdMedication;
use App\Models\CCD\CcdProblem;
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
use App\Notifications\ResetPassword;
use App\Services\UserService;
use DateTime;
use Faker\Factory;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Zizaco\Entrust\Traits\EntrustUserTrait;


class User extends Model implements AuthenticatableContract, CanResetPasswordContract, Serviceable
{

    use Authenticatable, CanResetPassword, Notifiable, SoftDeletes;

    use EntrustUserTrait {
        EntrustUserTrait::restore insteadof SoftDeletes;
    }

    use \Venturecraft\Revisionable\RevisionableTrait;

    public $rules = [
        'username'         => 'required',
        'email'            => 'required|email',
        'password'         => 'required',
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
    protected $revisionCreationsEnabled = true;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';
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
        'password',
    ];
    protected $dates = ['user_registered'];


    // for revisionable

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($user) {
            $user->providerInfo()->delete();
            $user->patientInfo()->delete();
            $user->carePlan()->delete();
            $user->patientCareTeamMembers()->delete();
        });

        self::restoring(function ($user) {
            $user->providerInfo()->restore();
            $user->patientInfo()->restore();
            $user->carePlan()->restore();
            $user->patientCareTeamMembers()->restore();
        });
    }

    public function getEmailForPasswordReset()
    {
        return $this->email;
    }


    // START RELATIONSHIPS

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
        return $this->hasMany(CcdAllergy::class, 'patient_id');
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
        return $this->hasMany(CcdMedication::class, 'patient_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function ccdProblems()
    {
        return $this->hasMany(CcdProblem::class, 'patient_id');
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

    public function patientInfo()
    {
        return $this->hasOne(PatientInfo::class, 'user_id', 'id');
    }

    public function nurseInfo()
    {
        return $this->hasOne(NurseInfo::class, 'user_id', 'id');
    }

    public function phoneNumbers()
    {
        return $this->hasMany('App\PhoneNumber', 'user_id', 'id');
    }

    public function carePlan()
    {
        return $this->hasOne(PatientCarePlan::class, 'patient_id', 'id');
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
    public function viewablePatientIds() : array
    {
        return User::ofType('participant')
            ->whereHas('practices', function ($q) {
                $q->whereIn('program_id', $this->viewableProgramIds());
            })
            ->pluck('id')
            ->all();
    }

    public function viewableProgramIds() : array
    {
        return $this->practices
            ->pluck('id')
            ->all();
    }

    public function viewableProviderIds()
    {
        // get all patients who are in the same programs
        $programIds = $this->viewableProgramIds();
        $patientIds = User::whereHas('practices', function ($q) use
        (
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
        $patientIds = User::whereHas('practices', function ($q) use
        (
            $programIds
        ) {
            $q->whereIn('program_id', $programIds);
        });

        $patientIds = $patientIds->pluck('id')->all();

        return $patientIds;
    }


    // END RELATIONSHIPS

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

    public function primaryProgramId()
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

    public function getHomePhoneNumberAttribute()
    {
        return $this->getPhoneAttribute();
    }

    public function getPhoneAttribute()
    {
        if (!$this->phoneNumbers) {
            return '';
        }
        $phoneNumber = $this->phoneNumbers->where('type', 'home')->first();
        if ($phoneNumber) {
            return $phoneNumber->number;
        } else {
            return '';
        }
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
        $careTeamMembers = $this->patientCareTeamMembers->where('type', 'member');
        if ($careTeamMembers->count() > 0) {
            foreach ($careTeamMembers as $careTeamMember) {
                $ct[] = $careTeamMember->member_user_id;
            }
        }

        return $ct;
    }

    public function setCareTeamAttribute($memberUserIds)
    {
        if (!is_array($memberUserIds)) {
            $this->patientCareTeamMembers()->where('type', 'member')->delete();

            return false; // must be array
        }
        $this->patientCareTeamMembers()->where('type', 'member')->whereNotIn('member_user_id',
            $memberUserIds)->delete();
        foreach ($memberUserIds as $memberUserId) {
            $careTeamMember = $this->patientCareTeamMembers()->where('type', 'member')->where('member_user_id',
                $memberUserId)->first();
            if ($careTeamMember) {
                $careTeamMember->member_user_id = $memberUserId;
            } else {
                $careTeamMember = new PatientCareTeamMember();
                $careTeamMember->user_id = $this->id;
                $careTeamMember->member_user_id = $memberUserId;
                $careTeamMember->type = 'member';
            }
            $careTeamMember->save();
        }

        return true;
    }

    public function patientCareTeamMembers()
    {
        return $this->hasMany(PatientCareTeamMember::class, 'user_id', 'id');
    }

    public function getSendAlertToAttribute()
    {
        $ctmsa = [];
        if (!$this->patientCareTeamMembers) {
            return '';
        }
        if ($this->patientCareTeamMembers->count() > 0) {
            foreach ($this->patientCareTeamMembers as $careTeamMember) {
                if ($careTeamMember->type == 'send_alert_to') {
                    $ctmsa[] = $careTeamMember->member_user_id;
                }
            }
        }

        return $ctmsa;
    }

    public function setSendAlertToAttribute($memberUserIds)
    {
        if (!is_array($memberUserIds)) {
            $this->patientCareTeamMembers()->where('type', 'send_alert_to')->delete();

            return false; // must be array
        }
        $this->patientCareTeamMembers()->where('type', 'send_alert_to')->whereNotIn('member_user_id',
            $memberUserIds)->delete();
        foreach ($memberUserIds as $memberUserId) {
            $careTeamMember = $this->patientCareTeamMembers()->where('type', 'send_alert_to')->where('member_user_id',
                $memberUserId)->first();
            if ($careTeamMember) {
                $careTeamMember->member_user_id = $memberUserId;
            } else {
                $careTeamMember = new PatientCareTeamMember();
                $careTeamMember->user_id = $this->id;
                $careTeamMember->member_user_id = $memberUserId;
                $careTeamMember->type = 'send_alert_to';
            }
            $careTeamMember->save();
        }

        return true;
    }

    public function getBillingProviderIDAttribute()
    {
        $bp = '';
        if (!$this->patientCareTeamMembers) {
            return '';
        }
        if ($this->patientCareTeamMembers->count() > 0) {
            foreach ($this->patientCareTeamMembers as $careTeamMember) {
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
            $this->patientCareTeamMembers()->where('type', 'billing_provider')->delete();

            return true;
        }
        $careTeamMember = $this->patientCareTeamMembers()->where('type', 'billing_provider')->first();
        if ($careTeamMember) {
            $careTeamMember->member_user_id = $value;
        } else {
            $careTeamMember = new PatientCareTeamMember();
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
        if (!$this->patientCareTeamMembers) {
            return '';
        }
        if ($this->patientCareTeamMembers->count() > 0) {
            foreach ($this->patientCareTeamMembers as $careTeamMember) {
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
            $this->patientCareTeamMembers()->where('type', 'lead_contact')->delete();

            return true;
        }
        $careTeamMember = $this->patientCareTeamMembers()->where('type', 'lead_contact')->first();
        if ($careTeamMember) {
            $careTeamMember->member_user_id = $value;
        } else {
            $careTeamMember = new PatientCareTeamMember();
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
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->careplan_qa_approver;
    }

    public function setCarePlanQAApproverAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->careplan_qa_approver = $value;
        $this->patientInfo->save();

        return true;
    }

    public function getCarePlanQADateAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->careplan_qa_date;
    }

    public function setCarePlanQADateAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->careplan_qa_date = $value;
        $this->patientInfo->save();

        return true;
    }

    public function getCarePlanProviderApproverAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->careplan_provider_approver;
    }

    public function setCarePlanProviderApproverAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->careplan_provider_approver = $value;
        $this->patientInfo->save();

        return true;
    }

    public function getCarePlanProviderApproverDateAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->careplan_provider_date;
    }

    public function setCarePlanProviderApproverDateAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->careplan_provider_date = $value;
        $this->patientInfo->save();

        return true;
    }

    public function getCarePlanStatusAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->careplan_status;
    }

    public function setCarePlanStatusAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->careplan_status = $value;
        $this->patientInfo->save();

        return true;
    }

    public function getCareplanLastPrintedAttribute()
    {
        if (!$this->patientInfo) {
            return '';
        }

        return $this->patientInfo->careplan_last_printed;
    }

    public function setCareplanLastPrintedAttribute($value)
    {
        if (!$this->patientInfo) {
            return '';
        }
        $this->patientInfo->careplan_last_printed = $value;
        $this->patientInfo->save();

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

    public function role($blogId = false)
    {
        if (!$blogId) {
            $blogId = $this->primaryProgramId();
        }
        $role = UserMeta::select('meta_value')->where('user_id', $this->id)->where('meta_key',
            'wp_' . $blogId . '_capabilities')->first();
        if (!$role) {
            return false;
        } else {
            $data = unserialize($role['meta_value']);

            return key($data);
        }
    }

    public function primaryProgram()
    {
        return $this->belongsTo(Practice::class, 'program_id', 'id');
    }


// MISC, these should be removed eventually

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

// user data scrambler

    public function service()
    {
        return new UserService();
    }

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
        $query->whereHas('roles', function ($q) use
        (
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
        $query->whereHas('roles', function ($q) use
        (
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
        $query->whereHas('locations', function ($q) use
        (
            $user
        ) {
            $q->whereIn('locations.id', $user->locations->pluck('id')->all());
        });
    }

    /**
     * Scope a query to intersect locations with the given user.
     *
     * @param $query
     * @param $user
     */
    public function scopeIntersectPracticesWith(
        $query,
        $user
    ) {
        $query->whereHas('practices', function ($q) use
        (
            $user
        ) {
            $q->whereIn('id', $user->viewableProgramIds());
        });
    }

    public function attachPractice($practice)
    {
        $id = is_object($practice)
            ? $practice->id
            : $practice;


        try {
            $this->practices()->attach($id);
        } catch (\Exception $e) {
            //check if this is a mysql exception for unique key constraint
            if ($e instanceof \Illuminate\Database\QueryException) {
                $errorCode = $e->errorInfo[1];
                if ($errorCode == 1062) {
                    //do nothing
                    //we don't actually want to terminate the program if we detect duplicates
                    //we just don't wanna add the row again
                    \Log::alert($e);
                }
            }
        }
    }

    public function practices()
    {
        return $this->belongsToMany(Practice::class, 'practice_user', 'user_id', 'program_id');
    }

    public function attachLocation($location)
    {
        $id = is_object($location)
            ? $location->id
            : $location;


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
                    \Log::alert($e);
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
        return ucwords($this->primaryProgram->display_name);
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
    public function billingProvider() : User
    {
        $billingProvider = $this->patientCareTeamMembers
            ->where('type', 'billing_provider')
            ->first();

        return $billingProvider->user ?? new User();
    }

    /**
     * Get billing provider.
     *
     * @return User
     */
    public function leadContact() : User
    {
        $leadContact = $this->patientCareTeamMembers
            ->where('type', 'lead_contact')
            ->first();

        return $leadContact->user ?? new User();
    }


    public function scopeWithCareTeamOfType(
        $query,
        $type
    ) {
        $query->with([
            'patientCareTeamMembers' => function ($q) use
            (
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
}

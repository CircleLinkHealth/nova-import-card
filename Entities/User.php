<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;
use App\Call;
use App\CareAmbassador;
use App\CarePlan;
use App\CareplanAssessment;
use App\ChargeableService;
use App\Constants;
use App\Exceptions\InvalidArgumentException;
use App\Facades\StringManipulation;
use CircleLinkHealth\Core\Filters\Filterable;
use App\ForeignId;
use App\Importer\Models\ImportedItems\DemographicsImport;
use App\Message;
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
use App\Notifications\CarePlanApprovalReminder;
use CircleLinkHealth\Core\Traits\Notifiable;
use App\Notifications\ResetPassword;
use App\Repositories\Cache\EmptyUserNotificationList;
use App\Repositories\Cache\UserNotificationList;
use App\Rules\PasswordCharacters;
use App\Services\UserService;
use App\TargetPatient;
use CircleLinkHealth\Customer\Traits\HasEmrDirectAddress;
use CircleLinkHealth\Customer\Traits\MakesOrReceivesCalls;
use CircleLinkHealth\Customer\Traits\SaasAccountable;
use App\Traits\TimezoneTrait;
use Carbon\Carbon;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use CircleLinkHealth\TwoFA\Entities\AuthyUser;
use DateTime;
use Faker\Factory;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Passport\HasApiTokens;
use Michalisantoniou6\Cerberus\Traits\CerberusSiteUserTrait;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

/**
 * CircleLinkHealth\Customer\Entities\User.
 *
 * @property int $id
 * @property int $count_ccm_time
 * @property string $username
 * @property string $program_id
 * @property string $password
 * @property string $email
 * @property \Carbon\Carbon $user_registered
 * @property int $user_status
 * @property int $auto_attach_programs
 * @property string $display_name
 * @property string $first_name
 * @property string $last_name
 * @property string|null $suffix
 * @property string $address
 * @property string $address2
 * @property string $city
 * @property string $state
 * @property string $zip
 * @property string|null $timezone
 * @property string $status
 * @property int $access_disabled
 * @property int|null $is_auto_generated
 * @property string|null $remember_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string|null $deleted_at
 * @property string|null $last_login
 * @property int $is_online
 * @property \CircleLinkHealth\TimeTracking\Entities\Activity[]|\Illuminate\Database\Eloquent\Collection $activities
 * @property \CircleLinkHealth\Customer\Entities\Appointment[]|\Illuminate\Database\Eloquent\Collection $appointments
 * @property \App\CareAmbassador $careAmbassador
 * @property \App\CareItem[]|\Illuminate\Database\Eloquent\Collection $careItems
 * @property \App\CarePlan $carePlan
 * @property \CircleLinkHealth\Customer\Entities\CarePerson[]|\Illuminate\Database\Eloquent\Collection $careTeamMembers
 * @property \App\Models\CCD\Allergy[]|\Illuminate\Database\Eloquent\Collection $ccdAllergies
 * @property \App\Models\CCD\CcdInsurancePolicy[]|\Illuminate\Database\Eloquent\Collection $ccdInsurancePolicies
 * @property \App\Models\CCD\Medication[]|\Illuminate\Database\Eloquent\Collection $ccdMedications
 * @property \App\Models\CCD\Problem[]|\Illuminate\Database\Eloquent\Collection $ccdProblems
 * @property \App\Models\MedicalRecords\Ccda[]|\Illuminate\Database\Eloquent\Collection $ccdas
 * @property \App\Comment[]|\Illuminate\Database\Eloquent\Collection $comment
 * @property \App\Models\CPM\CpmBiometric[]|\Illuminate\Database\Eloquent\Collection $cpmBiometrics
 * @property \App\Models\CPM\Biometrics\CpmBloodPressure $cpmBloodPressure
 * @property \App\Models\CPM\Biometrics\CpmBloodSugar $cpmBloodSugar
 * @property \App\Models\CPM\CpmLifestyle[]|\Illuminate\Database\Eloquent\Collection $cpmLifestyles
 * @property \App\Models\CPM\CpmMedicationGroup[]|\Illuminate\Database\Eloquent\Collection $cpmMedicationGroups
 * @property \App\Models\CPM\CpmMisc[]|\Illuminate\Database\Eloquent\Collection $cpmMiscs
 * @property \App\Models\CPM\CpmProblem[]|\Illuminate\Database\Eloquent\Collection $cpmProblems
 * @property \App\Models\CPM\Biometrics\CpmSmoking $cpmSmoking
 * @property \App\Models\CPM\CpmSymptom[]|\Illuminate\Database\Eloquent\Collection $cpmSymptoms
 * @property \App\Models\CPM\Biometrics\CpmWeight $cpmWeight
 * @property \App\Models\EmailSettings $emailSettings
 * @property \CircleLinkHealth\Customer\Entities\EmrDirectAddress[]|\Illuminate\Database\Eloquent\Collection $emrDirect
 * @property \App\ForeignId[]|\Illuminate\Database\Eloquent\Collection $foreignId
 * @property \App\User[]|\Illuminate\Database\Eloquent\Collection $forwardAlertsTo
 * @property mixed $active_date
 * @property mixed $age
 * @property mixed $agent_email
 * @property mixed $agent_name
 * @property mixed $agent_phone
 * @property mixed $agent_relationship
 * @property mixed $agent_telephone
 * @property mixed $billing_provider_i_d
 * @property string $billing_provider_name
 * @property mixed $birth_date
 * @property mixed $care_plan_provider_approver
 * @property mixed $care_plan_provider_approver_date
 * @property mixed $care_plan_q_a_approver
 * @property mixed $care_plan_q_a_date
 * @property mixed $care_plan_status
 * @property mixed $care_team
 * @property \Collection $care_team_receives_alerts
 * @property mixed $careplan_last_printed
 * @property mixed $careplan_mode
 * @property mixed $ccm_status
 * @property mixed $ccm_time
 * @property mixed $bhi_time
 * @property mixed $consent_date
 * @property mixed $daily_reminder_areas
 * @property mixed $daily_reminder_optin
 * @property mixed $daily_reminder_time
 * @property mixed $date_paused
 * @property mixed $date_withdrawn
 * @property mixed $emr_direct_address
 * @property mixed $full_name
 * @property mixed $full_name_with_id
 * @property mixed $gender
 * @property mixed $home_phone_number
 * @property mixed $hospital_reminder_areas
 * @property mixed $hospital_reminder_optin
 * @property mixed $hospital_reminder_time
 * @property mixed $lead_contact_i_d
 * @property mixed $m_r_n
 * @property mixed $mobile_phone_number
 * @property mixed $mrn_number
 * @property mixed $npi_number
 * @property mixed $phone
 * @property mixed $preferred_cc_contact_days
 * @property mixed $preferred_contact_language
 * @property mixed $preferred_contact_location
 * @property mixed $preferred_contact_method
 * @property mixed $preferred_contact_time
 * @property mixed $prefix
 * @property mixed $primary_phone
 * @property mixed $primary_practice_id
 * @property string $primary_practice_name
 * @property mixed $registration_date
 * @property mixed $send_alert_to
 * @property mixed $specialty
 * @property mixed $work_phone_number
 * @property UserPasswordsHistory|null $passwordsHistory
 * @property \App\Call[]|\Illuminate\Database\Eloquent\Collection $inboundCalls
 * @property \App\Message[]|\Illuminate\Database\Eloquent\Collection $inboundMessages
 * @property \CircleLinkHealth\Customer\Entities\Location[]|\Illuminate\Database\Eloquent\Collection $locations
 * @property \App\Note[]|\Illuminate\Database\Eloquent\Collection $notes
 * @property \Illuminate\Notifications\DatabaseNotification[]|\Illuminate\Notifications\DatabaseNotificationCollection
 *     $notifications
 * @property \CircleLinkHealth\Customer\Entities\Nurse $nurseInfo
 * @property \App\Observation[]|\Illuminate\Database\Eloquent\Collection $observations
 * @property \App\Call[]|\Illuminate\Database\Eloquent\Collection $outboundCalls
 * @property \App\Message[]|\Illuminate\Database\Eloquent\Collection $outboundMessages
 * @property \CircleLinkHealth\TimeTracking\Entities\Activity[]|\Illuminate\Database\Eloquent\Collection
 *     $patientActivities
 * @property \App\Importer\Models\ImportedItems\DemographicsImport[]|\Illuminate\Database\Eloquent\Collection
 *     $patientDemographics
 * @property \CircleLinkHealth\Customer\Entities\Patient $patientInfo
 * @property \CircleLinkHealth\Customer\Entities\PhoneNumber[]|\Illuminate\Database\Eloquent\Collection $phoneNumbers
 * @property \CircleLinkHealth\Customer\Entities\Practice[]|\Illuminate\Database\Eloquent\Collection $practices
 * @property \CircleLinkHealth\Customer\Entities\Practice $primaryPractice
 * @property \CircleLinkHealth\Customer\Entities\ProviderInfo $providerInfo
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 * @property \CircleLinkHealth\Customer\Entities\Role[]|\Illuminate\Database\Eloquent\Collection $roles
 * @property mixed $email_address
 * @property \App\CPRulesUCP[]|\Illuminate\Database\Eloquent\Collection $ucp
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User exceptType($type)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User hasBillingProvider($billing_provider_id)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User intersectLocationsWith($user)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User intersectPracticesWith($user)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User ofType($type)
 * @method static \Illuminate\Database\Query\Builder|\App\User onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereAccessDisabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereAutoAttachPrograms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCountCcmTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereIsAutoGenerated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereIsOnline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLastLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereSuffix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUserRegistered($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUserStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereZip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User withCareTeamOfType($type)
 * @method static \Illuminate\Database\Query\Builder|\App\User withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\User withoutTrashed()
 * @mixin \Eloquent
 * @property int|null $saas_account_id
 * @property int $skip_browser_checks Skip compatible browser checks when the user logs in
 * @property string|null $last_session_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\TimeTracking\Entities\Activity[]
 *     $activitiesAsProvider
 * @property-read \CircleLinkHealth\TwoFA\Entities\AuthyUser $authyUser
 * @property-read \App\CareplanAssessment $carePlanAssessment
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ChargeableService[] $chargeableServices
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Client[] $clients
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\Location[]
 *     $clinicalEmergencyContactLocations
 * @property-read \App\TargetPatient $ehrInfo
 * @property-read \CircleLinkHealth\Customer\Entities\EhrReportWriterInfo $ehrReportWriterInfo
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\User[]
 *     $forwardedAlertsBy
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\User[]
 *     $forwardedCarePlanApprovalEmailsBy
 * @property-read mixed $timezone_abbr
 * @property-read mixed $timezone_offset
 * @property-read mixed $timezone_offset_hours
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Call[] $inboundActivities
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Media[] $media
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\TimeTracking\Entities\PageTimer[]
 *     $pageTimersAsProvider
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary[]
 *     $patientSummaries
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\Permission[] $perms
 * @property-read \CircleLinkHealth\Customer\Entities\SaasAccount|null $saasAccount
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Token[] $tokens
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User careCoaches()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User
 *     filter(\App\Filters\QueryFilters $filters)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User isBhiChargeable()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User isBhiEligible()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User
 *     ofPractice($practiceId)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User
 *     practicesWhereHasRoles($roleIds, $onlyActive = false)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User query()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User
 *     whereLastSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User
 *     whereSaasAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User
 *     whereSkipBrowserChecks($value)
 */
class User extends BaseModel implements AuthenticatableContract, CanResetPasswordContract, HasMedia
{
    use Filterable,
        Authenticatable,
        CanResetPassword,
        CerberusSiteUserTrait,
        HasApiTokens,
        HasEmrDirectAddress,
        HasMediaTrait,
        Impersonate,
        MakesOrReceivesCalls,
        Notifiable,
        SaasAccountable,
        SoftDeletes,
        TimezoneTrait;
    const FORWARD_ALERTS_IN_ADDITION_TO_PROVIDER = 'forward_alerts_in_addition_to_provider';
    const FORWARD_ALERTS_INSTEAD_OF_PROVIDER     = 'forward_alerts_instead_of_provider';
    
    const FORWARD_CAREPLAN_APPROVAL_EMAILS_IN_ADDITION_TO_PROVIDER = 'forward_careplan_approval_emails_in_addition_to_provider';
    const FORWARD_CAREPLAN_APPROVAL_EMAILS_INSTEAD_OF_PROVIDER     = 'forward_careplan_approval_emails_instead_of_provider';
    
    public $patient_rules = [
        'daily_reminder_optin'    => 'required',
        'daily_reminder_time'     => 'required',
        'daily_reminder_areas'    => 'required',
        'hospital_reminder_optin' => 'required',
        'hospital_reminder_time'  => 'required',
        'hospital_reminder_areas' => 'required',
        'first_name'              => 'required',
        'last_name'               => 'required',
        'gender'                  => 'required',
        'mrn_number'              => 'required',
        'birth_date'              => 'required',
        'home_phone_number'       => 'required',
        'consent_date'            => 'required',
        'ccm_status'              => 'required',
        'program_id'              => 'required',
    ];
    
    public $phi = [
        'username',
        'email',
        'display_name',
        'first_name',
        'last_name',
        'suffix',
        'address',
        'address2',
        'city',
        'state',
        'zip',
    ];
    
    
    protected $attributes = [
        'timezone' => 'America/New_York',
    ];
    
    protected $dates = ['user_registered'];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'saas_account_id',
        'skip_browser_checks',
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
        'suffix',
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
        'last_session_id',
        'is_online',
    ];
    
    protected $hidden = [
        //@todo: Need to fix repository package. It does not validate hidden attributes. May temporarily comment out until then
        'password',
    ];
    
    protected $rules = [];
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->rules = [
            'username'              => 'required',
            'email'                 => 'required|email|unique:users,email',
            'password'              => ['required', 'filled', 'min:8', new PasswordCharacters()],
            'password_confirmation' => 'required|same:password',
        ];
    }
    
    public function activities()
    {
        return $this->hasMany(\CircleLinkHealth\TimeTracking\Entities\Activity::class, 'patient_id');
    }
    
    public function activitiesAsProvider()
    {
        return $this->hasMany(\CircleLinkHealth\TimeTracking\Entities\Activity::class, 'provider_id');
    }
    
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }
    
    /**
     * Assigns calls to a nurse.
     *
     * @param mixed $calls
     */
    public function assignOutboundCalls($calls)
    {
        $calls = Call::whereIn('id', parseIds($calls))->get();
        
        return $this->outboundCalls()->saveMany($calls);
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
                if (1062 == $errorCode) {
                    //return false so we know nothing was attached
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Attach Location(s).
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
            $this->locations()->attach($location);
        } catch (\Exception $e) {
            //check if this is a mysql exception for unique key constraint
            if ($e instanceof \Illuminate\Database\QueryException) {
                $errorCode = $e->errorInfo[1];
                if (1062 == $errorCode) {
                    //do nothing
                    //we don't actually want to terminate the program if we detect duplicates
                    //we just don't wanna add the row again
                }
            }
        }
    }
    
    public function attachPractice($practice, array $roleIds, $sendBillingReports = null)
    {
        $ids = parseIds($practice);
        
        if ( ! array_key_exists(0, $ids)) {
            throw new InvalidArgumentException('Could not parse a Practice id from the argument provided.');
        }
        
        $practiceId = $ids[0];
        
        $rolesForPractice = PracticeRoleUser::where('user_id', '=', $this->id)
                                            ->where('program_id', '=', $practiceId)
                                            ->get();
        
        //remove any roles not in $roleIds array
        foreach ($rolesForPractice as $roleForPractice) {
            //sometimes role_id is null
            if ( ! $roleForPractice->role_id) {
                continue;
            }
            
            if ( ! in_array($roleForPractice->role_id, $roleIds)) {
                //table does not have primary key, so need raw query
                $tableName = (new PracticeRoleUser())->getTable();
                $q         = "DELETE FROM $tableName where user_id = ? and program_id = ? and role_id = ?";
                \DB::delete($q, [$roleForPractice->user_id, $roleForPractice->program_id, $roleForPractice->role_id]);
            }
        }
        
        if (empty($roleIds)) {
            PracticeRoleUser::updateOrCreate(
                [
                    'user_id'    => $this->id,
                    'program_id' => $practiceId,
                ],
                null != $sendBillingReports
                    ? [
                    'send_billing_reports' => $sendBillingReports,
                ]
                    : []
            );
        } else {
            foreach ($roleIds as $r) {
                PracticeRoleUser::updateOrCreate(
                    [
                        'user_id'    => $this->id,
                        'program_id' => $practiceId,
                        'role_id'    => $r,
                    ],
                    null != $sendBillingReports
                        ? [
                        'send_billing_reports' => $sendBillingReports,
                    ]
                        : []
                );
            }
        }
    }
    
    public function authyUser()
    {
        return $this->hasOne(AuthyUser::class);
    }
    
    public function autocomplete()
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name() ?? $this->display_name,
            'program_id' => $this->program_id,
        ];
    }
    
    public function billableProblems()
    {
        return $this->ccdProblems()
                    ->whereNotNull('cpm_problem_id')
            //filter out unspecified diabetes
                    ->where('cpm_problem_id', '!=', 1)
                    ->with('icd10Codes')
                    ->where('billable', true);
    }
    
    public function billingCodes(Carbon $monthYear)
    {
        $summary = $this->patientSummaries()
                        ->where('month_year', $monthYear->toDateString())
                        ->with('chargeableServices')
                        ->has('chargeableServices')
                        ->first();
        
        if ( ! $summary) {
            return '';
        }
        
        return $summary->chargeableServices
            ->implode('code', ', ');
    }
    
    /**
     * Get billing provider.
     *
     * @return User
     */
    public function billingProvider()
    {
        return $this->careTeamMembers()->where('type', '=', CarePerson::BILLING_PROVIDER);
    }
    
    /**
     * Get billing provider User.
     *
     * @return User|null
     */
    public function billingProviderUser(): ?User
    {
        return $this->billingProvider->isEmpty()
            ? null
            : optional($this->billingProvider->first())->user;
    }
    
    public static function boot()
    {
        parent::boot();
        
        static::deleting(
            function ($user) {
                $user->providerInfo()->delete();
                $user->patientInfo()->delete();
                $user->carePlan()->delete();
                $user->careTeamMembers()->delete();
                $user->inboundCalls()->delete();
            }
        );
        
        static::restoring(
            function ($user) {
                $user->providerInfo()->restore();
                $user->patientInfo()->restore();
                $user->carePlan()->restore();
                $user->careTeamMembers()->restore();
            }
        );
    }
    
    public function cachedNotificationsList()
    {
        if (in_array(config('cache.default'), ['redis'])) {
            return new UserNotificationList($this->id);
        }
        
        return new EmptyUserNotificationList();
    }
    
    public function calls()
    {
        return $this->outboundCalls();
    }
    
    public function canApproveCarePlans()
    {
        return $this->hasPermissionForSite('care-plan-approve', $this->getPrimaryPracticeId());
    }
    
    /**
     * @return bool
     */
    public function canImpersonate()
    {
        return $this->isAdmin();
    }
    
    public function canQAApproveCarePlans()
    {
        return $this->hasPermissionForSite('care-plan-qa-approve', $this->getPrimaryPracticeId());
    }
    
    public function careAmbassador()
    {
        return $this->hasOne(CareAmbassador::class);
    }
    
    public function careItems()
    {
        return $this->belongsToMany(
            \App\CareItem::class,
            'care_item_user_values',
            'user_id',
            'care_item_id'
        )->withPivot('value');
    }
    
    public function carePlan()
    {
        return $this->hasOne(CarePlan::class, 'user_id', 'id');
    }
    
    /**
     * Temporary solution for `careplan_assessments.careplan_id` not being an actual `careplan_id` but a `user_id`.
     *
     * @see CPM-423
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function carePlanAssessment()
    {
        return $this->hasOne(CareplanAssessment::class, 'careplan_id');
    }
    
    public function careTeamMembers()
    {
        return $this->hasMany(CarePerson::class, 'user_id', 'id');
    }
    
    // CCD Models
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function ccdAllergies()
    {
        return $this->hasMany(Allergy::class, 'patient_id');
    }
    
    public function ccdas()
    {
        return $this->hasMany(Ccda::class, 'patient_id', 'id');
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
    
    public function chargeableServices()
    {
        return $this->morphToMany(ChargeableService::class, 'chargeable')
                    ->withPivot(['amount'])
                    ->withTimestamps();
    }
    
    /**
     * Delete all existing Phone Numbers and replace them with a new primary number.
     *
     * @param $number
     * @param $type
     * @param bool $isPrimary
     * @param mixed|null $extension
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
        
        return $this->phoneNumbers()->create(
            [
                'number'     => StringManipulation::formatPhoneNumber($number),
                'type'       => PhoneNumber::getTypes()[$type] ?? null,
                'is_primary' => $isPrimary,
                'extension'  => $extension,
            ]
        );
    }
    
    public function clinicalEmergencyContactLocations()
    {
        return $this->morphedByMany(Location::class, 'contactable', 'contacts')
                    ->withPivot('name')
                    ->wherePivot('name', '=', 'in_addition_to_billing_provider')
                    ->orWherePivot('name', '=', 'instead_of_billing_provider')
                    ->withTimestamps();
    }
    
    public function comment()
    {
        return $this->hasMany('App\Comment', 'user_id', 'id');
    }
    
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
    
    // CPM Models
    
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
    
    // CPM Biometrics
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cpmSmoking()
    {
        return $this->hasOne(CpmSmoking::class, 'patient_id');
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
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cpmWeight()
    {
        return $this->hasOne(CpmWeight::class, 'patient_id');
    }
    
    public function createNewUser(
        $email,
        $password
    ) {
        $this->username = $email;
        $this->email    = $email;
        $this->password = bcrypt($password);
        $this->save();
        
        return $this;
    }
    
    /**
     * (functions as an @ehrKeychain).
     *
     * Relates to TargetPatient class, contains all patient info for EHR
     * (ehr_practice_id, ehr_department_id etc)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ehrInfo()
    {
        return $this->hasOne(TargetPatient::class);
    }
    
    public function ehrReportWriterInfo()
    {
        return $this->hasOne(EhrReportWriterInfo::class, 'user_id', 'id');
    }
    
    public function emailSettings()
    {
        return $this->hasOne(EmailSettings::class);
    }
    
    public function firstOrNewProviderInfo()
    {
        if ( ! $this->hasRole('provider')) {
            return false;
        }
        
        return ProviderInfo::firstOrCreate(
            [
                'user_id' => $this->id,
            ]
        );
    }
    
    public function foreignId()
    {
        return $this->hasMany(ForeignId::class);
    }
    
    public function formattedBhiTime()
    {
        $seconds = $this->getBhiTime();
        
        return $this->formattedTime($seconds);
    }
    
    public function formattedCcmTime()
    {
        $seconds = $this->getCcmTime();
        
        return $this->formattedTime($seconds);
    }
    
    public function formattedTime($seconds)
    {
        $H = floor($seconds / 3600);
        $i = ($seconds / 60) % 60;
        $s = $seconds % 60;
        
        return sprintf('%02d:%02d:%02d', $H, $i, $s);
    }
    
    /**
     * Forward Alerts to another User.
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
     * Forward Alerts/Notifications to another User.
     * Attaches forwards to a user using forwardAlertsTo() relationship.
     *
     * @param mixed $receiverUserId
     * @param mixed $forwardTypeName
     */
    public function forwardTo($receiverUserId, $forwardTypeName)
    {
        $this->forwardAlertsTo()->attach(
            $receiverUserId,
            [
                'name' => $forwardTypeName,
            ]
        );
    }
    
    public function getActiveDate()
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        
        return $this->patientInfo->active_date;
    }
    
    public function getAge()
    {
        $from = new DateTime($this->getBirthDate());
        $to   = new DateTime('today');
        
        return $from->diff($to)->y;
    }
    
    public function getAgentEmail()
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        
        return $this->patientInfo->agent_email;
    }
    
    public function getAgentName()
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        
        return $this->patientInfo->agent_name;
    }
    
    public function getAgentPhone()
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        
        return $this->patientInfo->agent_telephone;
    }
    
    public function getAgentRelationship()
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        
        return $this->patientInfo->agent_relationship;
    }
    
    public function getAgentTelephone()
    {
        return $this->getAgentPhone();
    }
    
    public function getBhiTime()
    {
        return optional(
                   $this->patientSummaries()
                        ->select(['bhi_time', 'id'])
                        ->orderBy('id', 'desc')
                        ->whereMonthYear(Carbon::now()->startOfMonth())
                        ->first()
               )->bhi_time ?? 0;
    }
    
    public function getBillingProviderId()
    {
        $bp = '';
        if ( ! $this->careTeamMembers) {
            return '';
        }
        if ($this->careTeamMembers->count() > 0) {
            foreach ($this->careTeamMembers as $careTeamMember) {
                if ('billing_provider' == $careTeamMember->type) {
                    $bp = $careTeamMember->member_user_id;
                }
            }
        }
        
        return $bp;
    }
    
    /**
     * Get billing provider's full name.
     *
     * @return string
     */
    public function getBillingProviderName()
    {
        $billingProvider = $this->billingProviderUser();
        
        return $billingProvider
            ? $billingProvider->getFullName()
            : '';
    }
    
    /**
     * Get billing provider's phone.
     *
     * @return string
     */
    public function getBillingProviderPhone()
    {
        $billingProvider = $this->billingProviderUser();
        
        return $billingProvider
            ? $billingProvider->getPhone()
            : '';
    }
    
    public function getBirthDate()
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        
        return $this->patientInfo->birth_date;
    }
    
    public function getCareplanLastPrinted()
    {
        if ( ! $this->carePlan) {
            return '';
        }
        
        return $this->carePlan->last_printed;
    }
    
    public function getCareplanMode()
    {
        $careplanMode = null;
        
        if ($this->carePlan) {
            $careplanMode = $this->carePlan->mode;
        }
        
        if ( ! $careplanMode && $this->primaryPractice && $this->primaryPractice->settings) {
            $careplanMode = $this->primaryPractice->settings->first()->careplan_mode;
        }
        
        if ( ! $careplanMode) {
            $careplanMode = CarePlan::WEB;
        }
        
        return $careplanMode;
    }
    
    public function getCarePlanProviderApprover()
    {
        if ( ! $this->carePlan) {
            return '';
        }
        
        return $this->carePlan->provider_approver_id;
    }
    
    public function getCarePlanProviderApproverDate()
    {
        if ( ! $this->carePlan) {
            return '';
        }
        
        return $this->carePlan->provider_date;
    }
    
    public function getCarePlanQAApprover()
    {
        if ( ! $this->carePlan) {
            return '';
        }
        
        return $this->carePlan->qa_approver_id;
    }
    
    public function getCarePlanQADate()
    {
        if ( ! $this->carePlan) {
            return '';
        }
        
        return $this->carePlan->qa_date;
    }
    
    public function getCarePlanStatus()
    {
        if ( ! $this->carePlan) {
            return '';
        }
        
        return $this->carePlan->status;
    }
    
    public function getCareTeam()
    {
        $ct              = [];
        $careTeamMembers = $this->careTeamMembers->where('type', 'member');
        if ($careTeamMembers->count() > 0) {
            foreach ($careTeamMembers as $careTeamMember) {
                $ct[] = $careTeamMember->member_user_id;
            }
        }
        
        return $ct;
    }
    
    /**
     * Get the CarePeople who have subscribed to receive alerts for this Patient.
     * Returns a Collection of User objects, or an Empty Collection.
     *
     * @return Collection
     */
    public function getCareTeamReceivesAlerts()
    {
        if ( ! $this->primaryPractice->send_alerts) {
            return new Collection();
        }
        
        $careTeam = $this->careTeamMembers->where('alert', '=', true)
                                          ->keyBy('member_user_id')
                                          ->unique()
                                          ->values();
        
        $users = new Collection();
        
        //Get email forwarding
        foreach ($careTeam as $carePerson) {
            $forwardsTo = optional($carePerson->user)->forwardAlertsTo;
            if ($forwardsTo) {
                $forwards = $forwardsTo->whereIn(
                    'pivot.name',
                    [
                        User::FORWARD_ALERTS_IN_ADDITION_TO_PROVIDER,
                        User::FORWARD_ALERTS_INSTEAD_OF_PROVIDER,
                    ]
                );
                
                if ($forwards->isEmpty() && $carePerson->user) {
                    $users->push($carePerson->user);
                }
                
                foreach ($forwards as $forwardee) {
                    if (User::FORWARD_ALERTS_IN_ADDITION_TO_PROVIDER == $forwardee->pivot->name) {
                        $users->push($carePerson->user);
                        $users->push($forwardee);
                    }
                    
                    if (User::FORWARD_ALERTS_INSTEAD_OF_PROVIDER == $forwardee->pivot->name) {
                        $users->push($forwardee);
                    }
                }
            }
        }
        
        //Get clinical emergency contacts from locations
        foreach ($this->locations as $location) {
            if ( ! $location->clinicalEmergencyContact->isEmpty()) {
                $contact = $location->clinicalEmergencyContact->first();
                
                if (CarePerson::INSTEAD_OF_BILLING_PROVIDER == $contact->pivot->name) {
                    $users = new Collection();
                    $users->push($contact);
                    
                    return $users;
                }
                
                $users->push($contact);
            }
        }
        
        return $users;
    }
    
    public function getCcmStatus()
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        
        return $this->patientInfo->ccm_status;
    }
    
    public function getCcmTime()
    {
        return optional(
                   $this->patientSummaries()
                        ->select(['ccm_time', 'id'])
                        ->orderBy('id', 'desc')
                        ->whereMonthYear(Carbon::now()->startOfMonth())
                        ->first()
               )->ccm_time ?? 0;
    }
    
    public function getConsentDate()
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        
        return $this->patientInfo->consent_date;
    }
    
    public function getDailyReminderAreas()
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        
        return $this->patientInfo->daily_reminder_areas;
    }
    
    public function getDailyReminderOptin()
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        
        return $this->patientInfo->daily_reminder_optin;
    }
    
    public function getDailyReminderTime()
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        
        return $this->patientInfo->daily_reminder_time;
    }
    
    public function getDatePaused()
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        
        return $this->patientInfo->date_paused;
    }
    
    public function getDateUnreachable()
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        
        return $this->patientInfo->date_unreachable;
    }
    
    public function getDateWithdrawn()
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        
        return $this->patientInfo->date_withdrawn;
    }
    
    public function getDoctorFullNameWithSpecialty()
    {
        $specialty = '';
        
        if ($this->providerInfo) {
            $specialty = $this->getSpecialty() == $this->getSuffix()
                ? ''
                : "\n {$this->getSpecialty()}";
        }
        
        $fullName = $this->getFullName();
        $doctor   = starts_with(strtolower($fullName), 'dr.')
            ? ''
            : 'Dr. ';
        
        return $doctor.$fullName.$specialty;
    }
    
    public function getEmailForPasswordReset()
    {
        return $this->email;
    }
    
    public function getFirstName()
    {
        return ucfirst(strtolower($this->first_name));
    }
    
    public function getFullName()
    {
        $firstName = ucwords(strtolower($this->first_name));
        $lastName  = ucwords(strtolower($this->last_name));
        $suffix    = $this->getSuffix();
        
        return trim("${firstName} ${lastName} ${suffix}");
    }
    
    public function getFullNameWithId()
    {
        $name = $this->getFullName();
        
        return $name.' ('.$this->id.')';
    }
    
    public function getGender()
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        
        return $this->patientInfo->gender;
    }
    
    public function getHomePhoneNumber()
    {
        return $this->getPhone();
    }
    
    public function getHospitalReminderAreas()
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        
        return $this->patientInfo->hospital_reminder_areas;
    }
    
    public function getHospitalReminderOptin()
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        
        return $this->patientInfo->hospital_reminder_optin;
    }
    
    public function getHospitalReminderTime()
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        
        return $this->patientInfo->hospital_reminder_time;
    }
    
    public function getLastName()
    {
        return ucfirst(strtolower($this->last_name));
    }
    
    public function getLeadContactID()
    {
        $lc = [];
        if ( ! $this->careTeamMembers) {
            return '';
        }
        if ($this->careTeamMembers->count() > 0) {
            foreach ($this->careTeamMembers as $careTeamMember) {
                if ('lead_contact' == $careTeamMember->type) {
                    $lc = $careTeamMember->member_user_id;
                }
            }
        }
        
        return $lc;
    }
    
    public function getLegacyBhiNursePatientCacheKey($patientId)
    {
        if ( ! $this->id) {
            throw new \Exception('User ID not found.');
        }
        
        return 'hide_legacy_bhi_banner:'.$this->id.":${patientId}";
    }
    
    public function getMobilePhoneNumber()
    {
        if ( ! $this->phoneNumbers) {
            return '';
        }
        $phoneNumber = $this->phoneNumbers->where('type', 'mobile')->first();
        if ($phoneNumber) {
            return $phoneNumber->number;
        }
        
        return '';
    }
    
    public function getMRN()
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        
        return $this->patientInfo->mrn_number;
    }
    
    public function getMrnNumber()
    {
        return $this->getMRN();
    }
    
    public function getNoteChannelsText()
    {
        $channels = $this->primaryPractice->cpmSettings()->notesChannels();
        $i        = 1;
        $last     = count($channels);
        $output   = '';
        
        foreach ($channels as $channel) {
            $output .= (1 == $i
                    ? ''
                    : ', ')
                       .($i == $last && $i > 1
                    ? 'and '
                    : '').$channel;
            
            ++$i;
        }
        
        return $output;
    }
    
    public function getNotifiesText()
    {
        $careTeam = $this->getCareTeamReceivesAlerts();
        $i        = 1;
        $last     = $careTeam->count();
        $output   = '';
        
        foreach ($careTeam as $carePerson) {
            $output .= (1 == $i
                    ? ''
                    : ', ').($i == $last && $i > 1
                    ? 'and '
                    : '').$carePerson->getFullName();
            
            ++$i;
        }
        
        return $output;
    }
    
    public function getNpiNumber()
    {
        if ( ! $this->providerInfo) {
            return '';
        }
        
        return $this->providerInfo->npi_number;
    }
    
    public function getPhone()
    {
        if ( ! $this->phoneNumbers) {
            return '';
        }
        
        $phoneNumbers = $this->phoneNumbers;
        
        if (1 == count($phoneNumbers)) {
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
    
    public function getPreferredCcContactDays()
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        
        return $this->patientInfo->preferred_cc_contact_days;
    }
    
    public function getPreferredContactLanguage()
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        
        return $this->patientInfo->preferred_contact_language;
    }
    
    public function getPreferredContactLocation()
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        
        return $this->patientInfo->preferred_contact_location;
    }
    
    public function getPreferredContactMethod()
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        
        return $this->patientInfo->preferred_contact_method;
    }
    
    public function getPreferredContactTime()
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        
        return $this->patientInfo->preferred_contact_time;
    }
    
    public function getPreferredLocationAddress()
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $locationId = $this->patientInfo->preferred_contact_location;
        if (empty($locationId)) {
            return false;
        }
        
        return Location::find($locationId);
    }
    
    public function getPreferredLocationName()
    {
        if ( ! $this->patientInfo) {
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
    
    public function getPrefix()
    {
        if ( ! $this->providerInfo) {
            return '';
        }
        
        return $this->providerInfo->prefix;
    }
    
    public function getPrimaryPhone()
    {
        if ( ! $this->phoneNumbers) {
            return '';
        }
        $phoneNumber = $this->phoneNumbers->where('is_primary', 1)->first();
        if ($phoneNumber) {
            return $phoneNumber->number_with_dashes;
        }
        
        return '';
    }
    
    public function getPrimaryPracticeId()
    {
        return $this->program_id;
    }
    
    /**
     * Get primary practice's name.
     *
     * @return string
     */
    public function getPrimaryPracticeName()
    {
        return ucwords(optional($this->primaryPractice)->display_name);
    }
    
    /**
     * Get the User's Problems to populate the User header.
     *
     * @return array
     */
    public function getProblemsToMonitor()
    {
        return $this->ccdProblems
            ->sortBy('cpmProblem.name')
            ->pluck('cpmProblem.name', 'cpmProblem.id')
            ->all();
    }
    
    public function getRegistrationDate()
    {
        return $this->user_registered;
    }
    
    public function getSendAlertTo()
    {
        $ctmsa = [];
        if ( ! $this->careTeamMembers) {
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
    
    public function getSpecialty()
    {
        if ( ! $this->providerInfo) {
            return '';
        }
        
        return $this->providerInfo->specialty;
    }
    
    public function getSuffix()
    {
        return $this->suffix ?? '';
    }
    
    public function getUCP()
    {
        $userUcp     = $this->ucp()->with(
            [
                'item.meta',
                'item.question',
            ]
        )->get();
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
    
    public function getWorkPhoneNumber()
    {
        if ( ! $this->phoneNumbers) {
            return '';
        }
        $phoneNumber = $this->phoneNumbers->where('type', 'work')->first();
        if ($phoneNumber) {
            return $phoneNumber->number;
        }
        
        return '';
    }
    
    public function hasProblem($problem)
    {
        return ! $this->ccdProblems->where('cpm_problem_id', '=', $problem)->isEmpty();
    }
    
    public function hasScheduledCallToday()
    {
        return Call::where(
            function ($q) {
                $q->whereNull('type')
                  ->orWhere('type', '=', 'call');
            }
        )
                   ->where('inbound_cpm_id', $this->id)
                   ->where('status', 'scheduled')
                   ->where('scheduled_date', '=', Carbon::today()->format('Y-m-d'))
                   ->exists();
    }
    
    public function inboundActivities()
    {
        return $this->hasMany(Call::class, 'inbound_cpm_id', 'id');
    }
    
    /**
     * Calls made from CLH to the User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inboundCalls()
    {
        return $this->hasMany(Call::class, 'inbound_cpm_id', 'id')
                    ->where(
                        function ($q) {
                            $q->whereNull('type')
                              ->orWhere('type', '=', 'call');
                        }
                    );
    }
    
    public function inboundMessages()
    {
        return $this->hasMany(Message::class, 'receiver_cpm_id', 'id');
    }
    
    public function inboundScheduledActivities(Carbon $after = null)
    {
        return $this->inboundActivities()
                    ->where('status', '=', 'scheduled')
                    ->when(
                        $after,
                        function ($query) use ($after) {
                            return $query->where('scheduled_date', '>=', $after->toDateString());
                        }
                    )
                    ->where('called_date', '=', null);
    }
    
    public function inboundScheduledCalls(Carbon $after = null)
    {
        return $this->inboundCalls()
                    ->where('status', '=', 'scheduled')
                    ->when(
                        $after,
                        function ($query) use ($after) {
                            return $query->where('scheduled_date', '>=', $after->toDateString());
                        }
                    )
                    ->where('called_date', '=', null);
    }
    
    /**
     * Returns whether the user is an administrator.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->hasRole('administrator');
    }
    
    /**
     * Determine whether the User is BHI chargeable (ie. eligible and enrolled).
     *
     * @return bool
     */
    public function isBhi()
    {
        //Do we wanna cache this for a minute maybe?
//        return \Cache::remember("user:$this->id:is_bhi", 1, function (){
        return User::isBhiChargeable()
                   ->where('id', $this->id)
                   ->exists();
//        });
    }
    
    /**
     * Returns whether the user is a Care Coach (AKA Care Center).
     * A Care Coach can be employed from CLH ['care-center']
     * or not ['care-center-external'].
     *
     * @return bool
     */
    public function isCareCoach()
    {
        return $this->hasRole(['care-center', 'care-center-external']);
    }
    
    public function isCcm()
    {
        return $this->ccdProblems()
                    ->where('is_monitored', 1)
                    ->whereHas(
                        'cpmProblem',
                        function ($cpm) {
                            return $cpm->where('is_behavioral', 0);
                        }
                    )
                    ->exists();
    }
    
    public function isCCMCountable()
    {
        return $this->roles()->whereIn('name', Role::CCM_TIME_ROLES)->exists();
    }
    
    /**
     * Determines whether a patient is eligible to enroll.
     *
     * @return bool
     */
    public function isCcmEligible()
    {
        return 'to_enroll' == $this->getCcmStatus();
    }
    
    public function isInternalUser()
    {
        return $this->hasRole(Constants::CLH_INTERNAL_USER_ROLE_NAMES);
    }
    
    /**
     * Determine whether the User is Legacy BHI eligible.
     * "Legacy BHI Eligible" applies to a small number of patients who are BHI eligible, but consented before
     * 7/23/2018.
     * On 7/23/2018 we changed our Terms and Conditions to include BHI, so patients who consented before 7/23 need a
     * separate consent for BHI.
     *
     * @return bool
     */
    public function isLegacyBhiEligible()
    {
        //Do we wanna cache this for a minute maybe?
//        return \Cache::remember("user:$this->id:is_bhi_eligible", 1, function (){
        return User::isBhiEligible()
                   ->where('id', $this->id)
                   ->exists();
//        });
    }
    
    public function isPracticeStaff()
    {
        return $this->hasRole(Constants::PRACTICE_STAFF_ROLE_NAMES);
    }
    
    /**
     * Returns whether the user is an administrator.
     *
     * @return bool
     */
    public function isSoftwareOnly()
    {
        return $this->hasRole('software-only');
    }
    
    public function lastObservation()
    {
        return $this->observations()->orderBy('id', 'desc');
    }
    
    public function latestCcda()
    {
        return $this->ccdas()
                    ->orderBy('updated_at', 'desc')
                    ->first();
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
    
    public function linkToViewResource()
    {
        if ($this->isInternalUser()) {
            return route('admin.users.edit', ['id' => $this->id]);
        }
        
        if ($this->hasRole('participant')) {
            return route('patient.careplan.print', ['id' => $this->id]);
        }
        
        if ($this->isPracticeStaff()) {
            return route('provider.dashboard.manage.staff', ['practiceSlug' => $this->practices->first()->name]);
        }
    }
    
    public function locations()
    {
        return $this->belongsToMany(Location::class)
                    ->withTimestamps();
    }
    
    public function name()
    {
        return $this->display_name ?? ($this->getFirstName().$this->getLastName());
    }
    
    public function notes()
    {
        return $this->hasMany('App\Note', 'patient_id', 'id');
    }
    
    public function nurseInfo()
    {
        return $this->hasOne(Nurse::class, 'user_id', 'id');
    }
    
    public function observations()
    {
        return $this->hasMany('App\Observation', 'user_id', 'id');
    }
    
    /**
     * Calls made from the User to CLH.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function outboundCalls()
    {
        return $this->hasMany(Call::class, 'outbound_cpm_id', 'id')
                    ->where(
                        function ($q) {
                            $q->whereNull('type')
                              ->orWhere('type', '=', 'call');
                        }
                    );
    }
    
    /*public function hasScheduledCallThisWeek()
    {
        $weekStart = Carbon::now()->startOfWeek()->toDateString();
        $weekEnd = Carbon::now()->endOfWeek()->toDateString();

        return Call::where(function ($q) {
            $q->whereNull('type')
              ->orWhere('type', '=', 'call');
        })
                   ->where('outbound_cpm_id', $this->id)
                   ->where('status', 'scheduled')
                   ->whereBetween('scheduled_date', [$weekStart, $weekEnd])
                   ->exists();
    }*/
    
    public function outboundMessages()
    {
        return $this->hasMany(Message::class, 'sender_cpm_id', 'id');
    }
    
    public function pageTimersAsProvider()
    {
        return $this->hasMany(PageTimer::class, 'provider_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function passwordsHistory()
    {
        return $this->hasOne(UserPasswordsHistory::class, 'user_id');
    }
    
    public function patientActivities()
    {
        return $this->hasMany(\CircleLinkHealth\TimeTracking\Entities\Activity::class, 'patient_id', 'id');
    }
    
    public function patientDemographics()
    {
        return $this->hasMany(DemographicsImport::class, 'provider_id');
    }
    
    public function patientInfo()
    {
        return $this->hasOne(Patient::class, 'user_id', 'id');
    }
    
    public function patientList()
    {
        return User::intersectPracticesWith($this)
                   ->ofType('participant')
                   ->whereHas('patientInfo')
                   ->with(
                       [
                           'observations'    => function ($query) {
                               $query->where('obs_key', '!=', 'Outbound');
                               $query->orderBy('obs_date', 'DESC');
                               $query->first();
                           },
                           'careTeamMembers' => function ($q) {
                               $q->where('type', '=', CarePerson::BILLING_PROVIDER)
                                 ->with('user');
                           },
                           'phoneNumbers'    => function ($q) {
                               $q->where('type', '=', PhoneNumber::HOME);
                           },
                           'carePlan.providerApproverUser',
                           'primaryPractice',
                           'patientInfo',
                       ]
                   )
                   ->get();
    }
    
    public function patientsPendingApproval()
    {
        $approveOwnCarePlans = $this->providerInfo
            ? $this->providerInfo->approve_own_care_plans
            : false;
        
        return User::intersectPracticesWith($this)
                   ->ofType('participant')
                   ->whereHas('patientInfo')
                   ->whereHas(
                       'carePlan',
                       function ($q) {
                           $q->whereIn('status', [CarePlan::QA_APPROVED]);
                       }
                   )
                   ->whereHas(
                       'careTeamMembers',
                       function ($q) use ($approveOwnCarePlans) {
                           $q->where(
                               [
                                   ['type', '=', CarePerson::BILLING_PROVIDER],
                                   ['member_user_id', '=', $this->id],
                               ]
                           )
                             ->when(
                                 ! $approveOwnCarePlans,
                                 function ($q) {
                                     $q->orWhere(
                                         function ($q) {
                                             $q->whereHas(
                                                 'user',
                                                 function ($q) {
                                                     $q->whereHas(
                                                         'forwardAlertsTo',
                                                         function ($q) {
                                                             $q->where('contactable_id', $this->id)
                                                               ->orWhereIn(
                                                                   'name',
                                                                   [
                                                                       'forward_careplan_approval_emails_instead_of_provider',
                                                                       'forward_careplan_approval_emails_in_addition_to_provider',
                                                                   ]
                                                               );
                                                         }
                                                     );
                                                 }
                                             );
                                         }
                                     );
                                 }
                             );
                       }
                   )
                   ->with('primaryPractice')
                   ->with(
                       [
                           'observations' => function ($query) {
                               $query->where('obs_key', '!=', 'Outbound');
                               $query->orderBy('obs_date', 'DESC');
                               $query->first();
                           },
                           'phoneNumbers' => function ($q) {
                               $q->where('type', '=', PhoneNumber::HOME);
                           },
                       ]
                   );
    }
    
    public function patientSummaries()
    {
        return $this->hasMany(PatientMonthlySummary::class, 'patient_id');
    }
    
    public function phoneNumbers()
    {
        return $this->hasMany(PhoneNumber::class, 'user_id', 'id');
    }
    
    /**
     * Get the specified Practice, if it is related to this User
     * You can pass in a practice_id, practice_slug, or  CircleLinkHealth\Customer\Entities\Practice object.
     *
     * @param $practice
     *
     * @return mixed
     */
    public function practice($practice)
    {
        if (is_string($practice) && ! is_int($practice)) {
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
        
        if ( ! $practiceId) {
            return null;
        }
        
        return $this->practices()
                    ->where('program_id', '=', $practiceId)
                    ->first();
    }
    
    /**
     * Get the User's Primary Or Global Role.
     *
     * @return Role|null
     */
    public function practiceOrGlobalRole()
    {
        if ($this->practice($this->primaryPractice)) {
            $primaryPractice = $this->practice($this->primaryPractice);
            
            if ($primaryPractice->pivot->role_id) {
                return Role::find($primaryPractice->pivot->role_id);
            }
        }
        
        return $this->roles->first();
    }
    
    public function practices(
        bool $onlyActive = false,
        bool $onlyEnrolledPatients = false,
        array $ofRoleIds = null
    ) {
        return $this->belongsToMany(Practice::class, 'practice_role_user', 'user_id', 'program_id')
                    ->withPivot('role_id')
                    ->when(
                        $onlyActive,
                        function ($query) use ($onlyActive) {
                            return $query->where('active', '=', 1);
                        }
                    )
                    ->when(
                        $onlyEnrolledPatients,
                        function ($query) use ($onlyEnrolledPatients) {
                            //$query -> Practice Model
                            return $query->whereHas(
                                'patients',
                                function ($innerQuery) {
                                    //$innerQuery -> User Model
                                    return $innerQuery->whereHas(
                                        'patientInfo',
                                        function ($innerInnerQuery) {
                                            //$innerInnerQuery -> Patient model
                                            return $innerInnerQuery->where('ccm_status', '=', 'enrolled');
                                        }
                                    );
                                }
                            );
                        }
                    )
                    ->when(
                        $ofRoleIds,
                        function ($query) use ($ofRoleIds) {
                            return $query->whereIn('practice_role_user.role_id', $ofRoleIds);
                        }
                    )
                    ->withTimestamps();
    }
    
    public function practiceSettings()
    {
        if ( ! $this->primaryPractice) {
            return null;
        }
        
        return $this->primaryPractice->cpmSettings();
    }
    
    public function primaryPractice()
    {
        return $this->belongsTo(Practice::class, 'program_id', 'id');
    }
    
    public function primaryProgramId()
    {
        return $this->program_id;
    }
    
    public function primaryProgramName()
    {
        return $this->primaryPractice->display_name;
    }
    
    public function primaryProgramPhoneE164()
    {
        return formatPhoneNumberE164(optional($this->primaryPractice)->outgoing_phone_number);
    }
    
    public function problemsWithIcd10Code()
    {
        $billableProblems = new Collection();
        
        $ccdProblems = $this->ccdProblems()
                            ->with('icd10Codes')
                            ->with('cpmProblem')
                            ->whereNotNull('cpm_problem_id')
                            ->groupBy('cpm_problem_id')
                            ->get()
                            ->map(
                                function ($problem) use ($billableProblems) {
                                    $problem->billing_code = $problem->icd10Code();
                
                                    if ( ! $problem->billing_code) {
                                        return $problem;
                                    }
                
                                    if ($problem->icd10Codes()->exists()) {
                                        $billableProblems->prepend($problem);
                    
                                        return $problem;
                                    }
                
                                    $billableProblems->push($problem);
                
                                    return $problem;
                                }
                            );
        
        return $billableProblems;
    }
    
    public function providerInfo()
    {
        return $this->hasOne('CircleLinkHealth\Customer\Entities\ProviderInfo', 'user_id', 'id');
    }
    
    /**
     * Get the regular doctor.
     *
     * @return User
     */
    public function regularDoctor()
    {
        return $this->careTeamMembers()->where('type', '=', CarePerson::REGULAR_DOCTOR);
    }
    
    /**
     * Get regular doctor User.
     *
     * @return User|null
     */
    public function regularDoctorUser(): ?User
    {
        return $this->regularDoctor->isEmpty()
            ? null
            : $this->regularDoctor->first()->user;
    }
    
    public function rolesInPractice($practiceId)
    {
        return $this->roles()
                    ->wherePivot('program_id', '=', $practiceId)
                    ->get();
    }
    
    public function routeNotificationForTwilio()
    {
        return $this->getPrimaryPhone();
    }
    
    public function saasAccountName()
    {
        $saasAccount = $this->saasAccount;
        if ($saasAccount) {
            return $saasAccount->name;
        }
        $saasAccount = optional($this->primaryPractice)->saasAccount;
        if ( ! $saasAccount) {
            if (auth()->check()) {
                $saasAccount = auth()->user()->saasAccount;
            }
        }
        if ($saasAccount) {
            $this->saasAccount()
                 ->associate($saasAccount);
            
            return $saasAccount->name;
        }
        
        return 'CircleLink Health';
    }
    
    public function safe()
    {
        $careplan    = $this->carePlan()->first();
        $observation = $this->observations()->orderBy('id', 'desc')->first();
        $phone       = $this->phoneNumbers()->first();
        
        return [
            'id'                    => $this->id,
            'username'              => $this->username,
            'name'                  => $this->name() ?? $this->display_name,
            'address'               => $this->address,
            'city'                  => $this->city,
            'state'                 => $this->state,
            'specialty'             => $this->getSpecialty(),
            'program_id'            => $this->program_id,
            'status'                => $this->status,
            'user_status'           => $this->user_status,
            'is_online'             => $this->is_online,
            'patient_info'          => optional($this->patientInfo()->first())->safe(),
            'provider_info'         => $this->providerInfo()->first(),
            'billing_provider_name' => $this->getBillingProviderName(),
            'billing_provider_id'   => $this->getBillingProviderId(),
            'careplan'              => optional($careplan)->safe(),
            'last_read'             => optional($observation)->obs_date,
            'phone'                 => $this->getPhone() ?? optional($phone)->number,
            'created_at'            => optional($this->created_at)->format('c') ?? null,
            'updated_at'            => optional($this->updated_at)->format('c') ?? null,
        ];
    }
    
    public function scopeCareCoaches($query)
    {
        return $query->ofType(['care-center', 'care-center-external']);
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
        $query->whereHas(
            'roles',
            function ($q) use (
                $type
            ) {
                if (is_array($type)) {
                    $q->whereNotIn('name', $type);
                } else {
                    $q->where('name', '!=', $type);
                }
            }
        );
    }
    
    public function scopeHasBillingProvider(
        $query,
        $billing_provider_id
    ) {
        return $query->whereHas(
            'careTeamMembers',
            function ($k) use (
                $billing_provider_id
            ) {
                $k->whereType('billing_provider')
                  ->whereMemberUserId($billing_provider_id);
            }
        );
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
        $viewableLocations = $user->isAdmin()
            ? Location::all()->pluck('id')->all()
            : $user->locations->pluck('id')->all();
        
        return $query->whereHas(
            'locations',
            function ($q) use (
                $viewableLocations
            ) {
                $q->whereIn('locations.id', $viewableLocations);
            }
        );
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
        $viewablePractices = $user->viewableProgramIds();
        
        return $query->whereHas(
            'practices',
            function ($q) use (
                $viewablePractices
            ) {
                $q->whereIn('id', $viewablePractices);
            }
        );
    }
    
    /**
     * Scope for patients who can be charged for BHI.
     *
     * Conditions are:
     *      1. Patient is Enrolled
     *      2. Patient's Primary Practice is chargeable for BHI
     *      3. Patient has at least one BHI problem
     *      4. Patient has consented for BHI
     *
     * @param $builder
     *
     * @return mixed
     */
    public function scopeIsBhiChargeable($builder)
    {
        return $builder
            ->whereHas(
                'primaryPractice',
                function ($q) {
                    $q->hasServiceCode('CPT 99484');
                }
            )->whereHas(
                'patientInfo',
                function ($q) {
                    $q->enrolled();
                }
            )
            ->whereHas(
                'ccdProblems.cpmProblem',
                function ($q) {
                    $q->where('is_behavioral', true);
                }
            )
            ->where(
                function ($q) {
                    $q->whereHas(
                        'patientInfo',
                        function ($q) {
                            $q->where('consent_date', '>=', Patient::DATE_CONSENT_INCLUDES_BHI);
                        }
                    )->orWhereHas(
                        'notes',
                        function ($q) {
                            $q->where('type', '=', Patient::BHI_CONSENT_NOTE_TYPE);
                        }
                    );
                }
            );
    }
    
    /**
     * Scope for patients who are eligible for BHI.
     *
     * Conditions are:
     *      1. Patient is Enrolled
     *      2. Patient's Primary Practice is chargeable for BHI
     *      3. Patient has at least one BHI problem
     *
     * @param $builder
     *
     * @return mixed
     */
    public function scopeIsBhiEligible($builder)
    {
        return $builder
            ->whereHas(
                'primaryPractice',
                function ($q) {
                    $q->hasServiceCode('CPT 99484');
                }
            )
            ->whereHas(
                'patientInfo',
                function ($q) {
                    $q->enrolled()
                      ->where('consent_date', '<', Patient::DATE_CONSENT_INCLUDES_BHI);
                }
            )
            ->whereHas(
                'ccdProblems.cpmProblem',
                function ($q) {
                    $q->where('is_behavioral', true);
                }
            )
            ->whereDoesntHave(
                'notes',
                function ($q) {
                    $q->where('type', '=', Patient::BHI_REJECTION_NOTE_TYPE);
                }
            )
            ->whereDoesntHave(
                'notes',
                function ($q) {
                    $q->where('type', '=', Patient::BHI_CONSENT_NOTE_TYPE);
                }
            );
    }
    
    public function scopeOfPractice($query, $practiceId)
    {
        $practiceId = parseIds($practiceId);
        
        $query->whereHas(
            'practices',
            function ($q) use ($practiceId) {
                $q->whereIn('id', $practiceId);
            }
        );
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
        $query->whereHas(
            'roles',
            function ($q) use (
                $type
            ) {
                if (is_array($type)) {
                    $q->whereIn('name', $type);
                } else {
                    $q->where('name', '=', $type);
                }
            }
        );
    }
    
    /**
     * Scope the query to practices for which the user has at least one of the given roles.
     *
     * @param $query
     * @param array $roleIds
     * @param bool $onlyActive
     */
    public function scopePracticesWhereHasRoles($query, array $roleIds, bool $onlyActive = false)
    {
        $query->whereHas(
            'practices',
            function ($q) use ($roleIds, $onlyActive) {
                return $q
                    ->when(
                        $onlyActive,
                        function ($query) {
                            return $query->where('active', '=', 1);
                        }
                    )
                    ->whereIn('practice_role_user.role_id', $roleIds);
            }
        );
    }
    
    public function scopeWithCareTeamOfType(
        $query,
        $type
    ) {
        $query->with(
            [
                'careTeamMembers' => function ($q) use (
                    $type
                ) {
                    $q->where('type', $type)
                      ->with('user');
                },
            ]
        );
    }
    
    public function scramble()
    {
        $faker = Factory::create();
        if ( ! $faker) {
            return false;
        }
        
        //dd($randomUserInfo);
        // set random data
        $user = $this;
        $user->setFirstName($faker->firstName);
        $user->setLastName('Z-'.$faker->lastName);
        $user->username = $faker->userName;
        $user->password = $faker->password;
        $user->email    = $faker->freeEmail;
        $user->setMRN(rand());
        $user->setGender('M');
        $user->address  = $faker->address;
        $user->address2 = $faker->secondaryAddress;
        $user->city     = $faker->city;
        $user->state    = $faker->stateAbbr;
        $user->zip      = $faker->postcode;
        $user->setPhone('111-234-5678');
        $user->setWorkPhoneNumber('222-234-5678');
        $user->setMobilePhoneNumber('333-234-5678');
        $user->setBirthDate($faker->dateTimeThisCentury->format('Y-m-d'));
        $user->setAgentName('Secret Agent');
        $user->setAgentPhone('111-234-5678');
        $user->setAgentEmail('secret@agent.net');
        $user->setAgentRelationship('SA');
        $user->save();
    }
    
    /**
     * Send a CarePlan Approval reminder, if there are CarePlans pending approval.
     *
     * @param $numberOfCareplans
     * @param bool $force
     *
     * @return bool
     */
    public function sendCarePlanApprovalReminder($numberOfCareplans, $force = false)
    {
        if ( ! $this->shouldSendCarePlanApprovalReminder() && ! $force) {
            return false;
        }
        
        if ($numberOfCareplans < 1) {
            return false;
        }
        
        $this->loadMissing(['primaryPractice.settings']);
        
        $this->notify(new CarePlanApprovalReminder($numberOfCareplans));
        
        return true;
    }
    
    /**
     * Send the password reset notification.
     *
     * @param string $token
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }
    
    // MISC, these should be removed eventually
    
    public function service()
    {
        return app(UserService::class);
    }
    
    public function setActiveDate($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->active_date = $value;
        $this->patientInfo->save();
        
        return true;
    }
    
    public function setAgentEmail($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->agent_email = $value;
        $this->patientInfo->save();
        
        return true;
    }
    
    public function setAgentName($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->agent_name = $value;
        $this->patientInfo->save();
        
        return true;
    }
    
    public function setAgentPhone($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->agent_telephone = $value;
        $this->patientInfo->save();
        
        return true;
    }
    
    public function setAgentRelationship($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->agent_relationship = $value;
        $this->patientInfo->save();
        
        return true;
    }
    
    public function setAgentTelephone($value)
    {
        return $this->setAgentPhone($value);
    }
    
    public function setBillingProviderId($value)
    {
        if (empty($value)) {
            $this->careTeamMembers()->where('type', CarePerson::BILLING_PROVIDER)->delete();
            
            return true;
        }
        $careTeamMember = $this->careTeamMembers()->where('type', CarePerson::BILLING_PROVIDER)->first();
        if ($careTeamMember) {
            $careTeamMember->member_user_id = $value;
        } else {
            $careTeamMember                 = new CarePerson();
            $careTeamMember->user_id        = $this->id;
            $careTeamMember->member_user_id = $value;
            $careTeamMember->type           = CarePerson::BILLING_PROVIDER;
        }
        $careTeamMember->save();
        
        $this->load('billingProvider');
        $this->load('careTeamMembers');
        
        return true;
    }
    
    public function setBirthDate($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->birth_date = str_replace('-', '/', $value);
        $this->patientInfo->save();
        
        return true;
    }
    
    public function setCareplanLastPrinted($value)
    {
        if ( ! $this->carePlan) {
            return '';
        }
        $this->carePlan->last_printed = $value;
        $this->carePlan->save();
        
        return true;
    }
    
    public function setCarePlanProviderApprover($value)
    {
        if ( ! $this->carePlan) {
            return '';
        }
        $this->carePlan->provider_approver_id = $value;
        $this->carePlan->save();
        
        return true;
    }
    
    public function setCarePlanProviderApproverDate($value)
    {
        if ( ! $this->carePlan) {
            return '';
        }
        $this->carePlan->provider_date = $value;
        $this->carePlan->save();
        
        return true;
    }
    
    public function setCarePlanQAApprover($value)
    {
        if ( ! $this->carePlan) {
            return '';
        }
        $this->carePlan->qa_approver_id = $value;
        $this->carePlan->save();
        
        return true;
    }
    
    public function setCarePlanQADate($value)
    {
        if ( ! $this->carePlan) {
            return '';
        }
        $this->carePlan->qa_date = $value;
        $this->carePlan->save();
        
        return true;
    }
    
    public function setCarePlanStatus($value)
    {
        if ( ! $this->carePlan) {
            return '';
        }
        $this->carePlan->status = $value;
        $this->carePlan->save();
        
        $this->load('carePlan');
        
        return true;
    }
    
    public function setCareTeam(array $memberUserIds)
    {
        if ( ! is_array($memberUserIds)) {
            $this->careTeamMembers()->where('type', 'member')->delete();
            
            return false; // must be array
        }
        $this->careTeamMembers()->where('type', 'member')->whereNotIn(
            'member_user_id',
            $memberUserIds
        )->delete();
        foreach ($memberUserIds as $memberUserId) {
            $careTeamMember = $this->careTeamMembers()->where('type', 'member')->where(
                'member_user_id',
                $memberUserId
            )->first();
            if ($careTeamMember) {
                $careTeamMember->member_user_id = $memberUserId;
            } else {
                $careTeamMember                 = new CarePerson();
                $careTeamMember->user_id        = $this->id;
                $careTeamMember->member_user_id = $memberUserId;
                $careTeamMember->type           = 'member';
            }
            $careTeamMember->save();
        }
        
        return true;
    }
    
    public function setCcmStatus($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        
        $this->patientInfo->ccm_status = $value;
    }
    
    public function setDailyReminderAreas($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->daily_reminder_areas = $value;
        $this->patientInfo->save();
        
        return true;
    }
    
    public function setDailyReminderOptin($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->daily_reminder_optin = $value;
        $this->patientInfo->save();
        
        return true;
    }
    
    public function setDailyReminderTime($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->daily_reminder_time = $value;
        $this->patientInfo->save();
        
        return true;
    }
    
    public function setDatePaused($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->date_paused = $value;
        $this->patientInfo->save();
        
        return true;
    }
    
    public function setDateUnreachable($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->date_unreachable = $value;
        $this->patientInfo->save();
        
        return true;
    }
    
    public function setDateWithdrawn($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->date_withdrawn = $value;
        $this->patientInfo->save();
        
        return true;
    }
    
    public function setEmailAddress($value)
    {
        return $this->email = $value;
    }
    
    public function setFirstName($value)
    {
        $this->attributes['first_name'] = ucwords($value);
        $this->display_name             = $this->getFullName();
    }
    
    public function setGender($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->gender = $value;
        $this->patientInfo->save();
        
        return true;
    }
    
    public function setHomePhoneNumber($value)
    {
        return $this->setPhone($value);
    }
    
    public function setHospitalReminderAreas($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->hospital_reminder_areas = $value;
        $this->patientInfo->save();
        
        return true;
    }
    
    public function setHospitalReminderOptin($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->hospital_reminder_optin = $value;
        $this->patientInfo->save();
        
        return true;
    }
    
    public function setHospitalReminderTime($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->hospital_reminder_time = $value;
        $this->patientInfo->save();
        
        return true;
    }
    
    public function setLastName($value)
    {
        $this->attributes['last_name'] = $value;
        $this->display_name            = $this->getFullName();
    }
    
    public function setLeadContactID($value)
    {
        if (empty($value)) {
            $this->careTeamMembers()->where('type', 'lead_contact')->delete();
            
            return true;
        }
        $careTeamMember = $this->careTeamMembers()->where('type', 'lead_contact')->first();
        if ($careTeamMember) {
            $careTeamMember->member_user_id = $value;
        } else {
            $careTeamMember                 = new CarePerson();
            $careTeamMember->user_id        = $this->id;
            $careTeamMember->member_user_id = $value;
            $careTeamMember->type           = 'lead_contact';
        }
        $careTeamMember->save();
        
        return true;
    }
    
    public function setMobilePhoneNumber($value)
    {
        if ( ! $this->phoneNumbers) {
            return '';
        }
        $phoneNumber = $this->phoneNumbers->where('type', 'mobile')->first();
        if ($phoneNumber) {
            $phoneNumber->number = $value;
        } else {
            $phoneNumber          = new PhoneNumber();
            $phoneNumber->user_id = $this->id;
            $phoneNumber->number  = $value;
            $phoneNumber->type    = 'mobile';
        }
        $phoneNumber->save();
        
        return true;
    }
    
    public function setMRN($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->mrn_number = $value;
        $this->patientInfo->save();
        
        return true;
    }
    
    public function setMrnNumber($value)
    {
        return $this->setMRN($value);
    }
    
    public function setNpiNumber($value)
    {
        if ( ! $this->providerInfo) {
            return '';
        }
        $this->providerInfo->npi_number = $value;
        $this->providerInfo->save();
    }
    
    public function setPhone($value)
    {
        if ( ! $this->phoneNumbers) {
            return '';
        }
        $phoneNumber = $this->phoneNumbers->where('type', 'home')->first();
        if ($phoneNumber) {
            $phoneNumber->number = $value;
        } else {
            $phoneNumber             = new PhoneNumber();
            $phoneNumber->user_id    = $this->id;
            $phoneNumber->is_primary = 1;
            $phoneNumber->number     = $value;
            $phoneNumber->type       = 'home';
        }
        $phoneNumber->save();
        
        return true;
    }
    
    public function setPreferredCcContactDays($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->preferred_cc_contact_days = $value;
        $this->patientInfo->save();
        
        return true;
    }
    
    public function setPreferredContactLanguage($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->preferred_contact_language = $value;
        $this->patientInfo->save();
        
        return true;
    }
    
    public function setPreferredContactLocation($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->preferred_contact_location = $value;
        $this->patientInfo->save();
        
        return true;
    }
    
    public function setPreferredContactMethod($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->preferred_contact_method = $value;
        $this->patientInfo->save();
        
        return true;
    }
    
    public function setPreferredContactTime($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->preferred_contact_time = $value;
        $this->patientInfo->save();
        
        return true;
    }
    
    public function setPrefix($value)
    {
        if ( ! $this->providerInfo) {
            return '';
        }
        $this->providerInfo->prefix = $value;
        $this->providerInfo->save();
    }
    
    public function setRegistrationDate($value)
    {
        $this->user_registered = $value;
        $this->save();
        
        return true;
    }
    
    public function setSendAlertTo($memberUserIds)
    {
        if ( ! is_array($memberUserIds)) {
            $this->careTeamMembers()->where('alert', '=', true)->delete();
            
            return false; // must be array
        }
        $this->careTeamMembers()->where('alert', '=', true)->whereNotIn(
            'member_user_id',
            $memberUserIds
        )->delete();
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
    
    public function setSpecialty($value)
    {
        if ( ! $this->providerInfo) {
            return '';
        }
        $this->providerInfo->specialty = $value;
        $this->providerInfo->save();
    }
    
    public function setWorkPhoneNumber($value)
    {
        if ( ! $this->phoneNumbers) {
            return '';
        }
        $phoneNumber = $this->phoneNumbers->where('type', 'work')->first();
        if ($phoneNumber) {
            $phoneNumber->number = $value;
        } else {
            $phoneNumber          = new PhoneNumber();
            $phoneNumber->user_id = $this->id;
            $phoneNumber->number  = $value;
            $phoneNumber->type    = 'work';
        }
        $phoneNumber->save();
        
        return true;
    }
    
    /**
     * @return bool
     */
    public function shouldSendCarePlanApprovalReminder()
    {
        $settings = $this->emailSettings()->firstOrNew([]);
        
        return EmailSettings::DAILY == $settings->frequency
            ? true
            : (EmailSettings::WEEKLY == $settings->frequency) && 1 == Carbon::today()->dayOfWeek
                ? true
                : (EmailSettings::MWF == $settings->frequency) &&
                  (1 == Carbon::today()->dayOfWeek
                   || 3 == Carbon::today()->dayOfWeek
                   || 5 == Carbon::today()->dayOfWeek)
                    ? true
                    : false;
    }
    
    /**
     * Determines wheter to show the BHI banner to the logged in user, for a given patient.
     *
     * @param User $patient
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function shouldShowLegacyBhiBannerFor(User $patient)
    {
        return $this->hasPermissionForSite('legacy-bhi-consent-decision.create', $patient->program_id)
               && is_a($patient, self::class)
               && $patient->isLegacyBhiEligible()
               && $patient->billingProviderUser()
               && ($patient->hasScheduledCallToday() && ! Cache::has(
                    $this->getLegacyBhiNursePatientCacheKey($patient->id)
                ));
    }
    
    public function ucp()
    {
        return $this->hasMany('App\CPRulesUCP', 'user_id', 'id');
    }
    
    public function userConfig()
    {
        $key        = 'wp_'.$this->primaryProgramId().'_user_config';
        $userConfig = $this->meta->where('meta_key', $key)->first();
        if ( ! $userConfig) {
            return false;
        }
        
        return unserialize($userConfig['meta_value']);
    }
    
    /**
     * @return array
     */
    public function viewablePatientIds(): array
    {
        return User::ofType('participant')
                   ->whereHas(
                       'practices',
                       function ($q) {
                           $q->whereIn('program_id', $this->viewableProgramIds());
                       }
                   )
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
        $patientIds = User::whereHas(
            'practices',
            function ($q) use (
                $programIds
            ) {
                $q->whereIn('program_id', $programIds);
            }
        );
        
        $patientIds->whereHas(
            'roles',
            function ($q) {
                $q->where('name', '=', 'provider');
            }
        );
        
        return $patientIds->pluck('id')->all();
    }
    
    public function viewableUserIds()
    {
        // get all patients who are in the same programs
        $programIds = $this->viewableProgramIds();
        $patientIds = User::whereHas(
            'practices',
            function ($q) use (
                $programIds
            ) {
                $q->whereIn('program_id', $programIds);
            }
        );
        
        return $patientIds->pluck('id')->all();
    }
    
    public function hasCcda()
    {
        if ( ! $this->id) {
            return null;
        }
        
        $cacheKey = str_replace('{$userId}', $this->id, Constants::CACHE_USER_HAS_CCDA);
        
        return Cache::rememberForever(
            $cacheKey,
            function () {
                return $this->ccdas()->exists();
            }
        );
    }
}

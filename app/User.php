<?php namespace App;

use App\CLH\CCD\ImportedItems\DemographicsImport;
use DateTime;
use Hautelook\Phpass\PasswordHash;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Support\Facades\App;
use MikeMcLin\WpPassword\Facades\WpPassword;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Auth;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use SoftDeletes;

	use Authenticatable, CanResetPassword, EntrustUserTrait;

	// for revisionable
	use \Venturecraft\Revisionable\RevisionableTrait;
	protected $revisionCreationsEnabled = true;

	/**
	 * The connection name for the model.
	 *
	 * @var string
	 */
	protected $connection = 'mysql_no_prefix';

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'wp_users';

	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primaryKey = 'ID';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['user_login', 'user_pass', 'user_nicename', 'user_email', 'user_url', 'user_registered', 'user_activation_log', 'user_status', 'auto_attach_programs', 'display_name', 'spam', 'password',
	'first_name',
	'last_name',
	'address',
	'city',
	'state',
	'zip',
	'is_auto_generated'];

	protected $hidden = ['user_pass'];

	protected $dates = ['user_registered'];

	/**
	 * @todo: make timestamps work
	 */
	public $timestamps = false;

	public $rules = array(
		'user_login'             => 'required',                        // just a normal required validation
		'user_email'            => 'required|email',     // required and must be unique in the wp_users table
		'user_pass'         => 'required',
		'user_pass_confirm' => 'required|same:user_pass',           // required and has to match the password field
		//'user_nicename'         => 'required',
		//'user_status'         => 'required',
		//'display_name'         => 'required',
	);

	public $patient_rules = array(
		//"user_id" => "required",
		"daily_reminder_optin" => "required",
		"daily_reminder_time" => "required",
		"daily_reminder_areas" => "required",
		"hospital_reminder_optin" => "required",
		"hospital_reminder_time" => "required",
		"hospital_reminder_areas" => "required",
		"qualification" => "",
		"specialty" => "",
		"npi_number" => "",
		"first_name" => "required",
		"last_name" => "required",
		"gender" => "required",
		"mrn_number" => "required",
		"birth_date" => "required",
		"home_phone_number" => "required",
		"email" => "",
		"address" => "",
		"city" => "",
		"state" => "",
		"zip" => "",
		"preferred_contact_time" => "required",
		"timezone" => "required",
		"consent_date" => "required",
		"ccm_status" => "required",
		"program_id" => "required"
	);



	// WordPress uses differently named fields for create and update fields than Laravel does
	const CREATED_AT = 'post_date';
	const UPDATED_AT = 'post_modified';


	// for revisionable
	public static function boot()
	{
		parent::boot();

		static::deleting(function($user) {
			$user->providerInfo()->delete();
			$user->patientInfo()->delete();
			$user->patientCarePlans()->delete();
			$user->patientCareTeamMembers()->delete();
		});

		self::restoring(function ($user) {
			$user->providerInfo()->restore();
			$user->patientInfo()->restore();
			$user->patientCarePlans()->restore();
			$user->patientCareTeamMembers()->restore();
		});
	}


	public function getAuthIdentifier()
	{
		return $this->getKey();
	}

    public function getAuthPassword()
	{
		return $this->user_pass;
	}

    public function getEmailForPasswordReset()
	{
		return $this->user_email;
	}


	// START RELATIONSHIPS

	public function foreignId()
	{
		return $this->hasMany(ForeignId::class);
	}

	public function locations()
	{
		return $this->belongsToMany(Location::class);
	}

    public function meta()
	{
		return $this->hasMany('App\UserMeta', 'user_id', 'ID');
	}

	public function patientDemographics()
	{
		return $this->hasMany(DemographicsImport::class, 'provider_id');
	}

    public function comment()
	{
		return $this->hasMany('App\Comment', 'user_id', 'ID');
	}

    public function observations()
	{
		return $this->hasMany('App\Observation', 'user_id', 'ID');
	}

	public function careItems()
	{
		return $this->belongsToMany('App\CareItem', 'care_item_user_values', 'user_id', 'care_item_id')->withPivot('value');
	}

    public function activities()
	{
		return $this->hasMany('App\Activity');
	}

    public function ucp()
	{
		return $this->hasMany('App\CPRulesUCP', 'user_id', 'ID');
	}

	public function providerInfo()
	{
		return $this->hasOne('App\ProviderInfo', 'user_id', 'ID');
	}

	public function patientInfo()
	{
		return $this->hasOne('App\PatientInfo', 'user_id', 'ID');
	}

	public function phoneNumbers()
	{
		return $this->hasOne('App\PhoneNumber', 'user_id', 'ID');
	}

	public function patientCarePlans()
	{
		return $this->hasOne('App\PatientCarePlan', 'user_id', 'ID');
	}

	public function patientCareTeamMembers()
	{
		return $this->hasOne('App\PatientCareTeamMember', 'user_id', 'ID');
	}


	// END RELATIONSHIPS


	public function viewableProgramIds() {
		$programIds = $this->programs()->lists('blog_id');
		return $programIds;
	}

	public function viewablePatientIds() {
		// get all patients who are in the same programs
		$programIds = $this->viewableProgramIds();
		$patientIds = User::whereHas('programs', function ($q) use ($programIds) {
			$q->whereIn('program_id', $programIds);
		});

		if(!Auth::user()->can('admin-access')) {
			$patientIds->whereHas('roles', function ($q) {
				$q->where('name', '=', 'participant');
			});
		}

		$patientIds = $patientIds->lists('ID');
		return $patientIds;
	}

	public function viewableUserIds() {
		// get all patients who are in the same programs
		$programIds = $this->viewableProgramIds();
		$patientIds = User::whereHas('programs', function ($q) use ($programIds) {
			$q->whereIn('program_id', $programIds);
		});

		$patientIds = $patientIds->lists('ID');
		return $patientIds;
	}

    public function userConfig(){
		$key = 'wp_'.$this->blogId().'_user_config';
		$userConfig = $this->meta->where('meta_key', $key)->first();
		if(!$userConfig) {
			return false;
		} else {
			return unserialize($userConfig['meta_value']);
		}
	}

    public function userMeta($key=null){
		$userMeta = $this->meta->lists('meta_value', 'meta_key');
		$userMeta['user_config'] = $this->userConfig();
		if(!$userMeta) {
			return false;
		} else {
			return $userMeta;
		}
	}

	public function getUserMetaByKey($key)
	{
		$value = '';
		$meta = $this->meta->where('meta_key', $key)->first();
		if(!empty($meta && $meta->meta_value != '' ) ) {
			$value = $meta->meta_value;
		}
		return $value;
	}

	public function setUserMetaByKey($key, $value)
	{
		$meta = $this->meta->where('meta_key', $key)->first();
		if( !empty($meta) ) {
			$meta->meta_value = $value;
			$meta->save();
		} else {
			$meta = new UserMeta;
			$meta->meta_key = $key;
			$meta->meta_value = $value;
			$meta->user_id = $this->ID;
			$this->meta()->save($meta);
			$this->load('meta');
		}
		return true;
	}

	public function getUserConfigByKey($key)
	{
		$userConfig = $this->userConfig();
		return (isset($userConfig[$key])) ? $userConfig[$key] : '';
	}

	public function setUserConfigByKey($key, $value)
	{
		$configKey = 'wp_'.$this->blogId().'_user_config';
		$userConfig = UserMeta::where('user_id', $this->ID)->where('meta_key',$configKey)->first();
		if(empty($userConfig)) {
			$userConfig = new UserMeta;
			$userConfig->meta_key = $configKey;
			$userConfig->meta_value = serialize(array());
			$userConfig->user_id = $this->ID;
			$userConfig->save();
			$userConfigArray = array();
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

    // START ATTRIBUTES
	public function setUserAttributeByKey($key, $value)
	{

		$func = create_function('$c', 'return strtoupper($c[1]);');
		$attribute = preg_replace_callback('/_([a-z])/', $func, $key);

		// these are now on User model, no longer remote attributes:
		if( $key === 'firstName' || $key == 'lastName' ) {
			return true;
		}

		// hack overrides and depreciated keys, @todo fix these
		if($attribute == 'careplanProviderDate') {
			$attribute = 'careplanProviderApproverDate';
		} else if($attribute == 'mrnNumber') {
			$attribute = 'mrn';
		} else if($attribute == 'studyPhoneNumber') {
			return false;
		} else if($attribute == 'billingProvider') {
			$attribute = 'billingProviderID';
		} else if($attribute == 'leadContact') {
			$attribute = 'leadContactID';
		} else if($attribute == 'programId') {
			return false;
		}

		// serialize any arrays
		if(is_array($value)) {
			$value = serialize($value);
		}

		// get before for debug
		$before = $this->$attribute;
		if(is_array($before)) {
			$before = serialize($before);
		}

		// call save attribute
		echo '----'.$attribute .'<br />';
		$this->$attribute = $value;
		$this->save();

		// get after for debug
		$after = $this->$attribute;
		if(is_array($after)) {
			$after = serialize($after);
		}
		//echo $attribute .' -- Before: ' . $before . '<br />';
		//echo $attribute .' -- Value: ' . $value . '<br />';
		//echo $attribute .' -- After: ' . $after . '<br />';
		return true;
	}

    // basic attributes

	/*
	// first_name
    public function getFirstNameAttribute() {
		return $this->first_name;
	}
	public function setFirstNameAttribute($value) {
		$this->first_name = $value;
		return true;
	}

	// last_name
    public function getLastNameAttribute() {
		return $this->last_name;
	}
	public function setLastNameAttribute($value) {
		$this->last_name = $value;
		return true;
	}
	*/

	// full name
	public function getFullNameAttribute() {
		$firstName = $this->first_name;
		$lastName = $this->last_name;
		return $firstName . ' ' . $lastName;
	}

	// full name w/ id
	public function getFullNameWithIdAttribute() {
		$name = $this->fullName;
		return $name . ' ('.$this->ID.')';
	}

	// preferred_cc_contact_days
	public function getPreferredCcContactDaysAttribute() {
		if(!$this->patientInfo) return '';
		return $this->patientInfo->preferred_cc_contact_days;
	}
	public function setPreferredCcContactDaysAttribute($value) {
		if(!$this->patientInfo) return '';
		$this->patientInfo->preferred_cc_contact_days = $value;
		$this->patientInfo->save();
		return true;
	}

	// active_date
	public function getActiveDateAttribute() {
		if(!$this->patientInfo) return '';
		return $this->patientInfo->active_date;
	}
	public function setActiveDateAttribute($value) {
		if(!$this->patientInfo) return '';
		$this->patientInfo->active_date = $value;
		$this->patientInfo->save();
		return true;
	}

	// registration_date
	public function getRegistrationDateAttribute() {
		if(!$this->patientInfo) return '';
		return $this->patientInfo->registration_date;
	}
	public function setRegistrationDateAttribute($value) {
		if(!$this->patientInfo) return '';
		$this->patientInfo->registration_date = $value;
		$this->patientInfo->save();
		return true;
	}

	// specialty
	public function getSpecialtyAttribute() {
		if(!$this->providerInfo) {
			return '';
		}
		return $this->providerInfo->specialty;
	}
	public function setSpecialtyAttribute($value) {
		if(!$this->providerInfo) {
			return '';
		}
		$this->providerInfo->specialty = $value;
		$this->providerInfo->save();
	}

	// npi_number
	public function getNpiNumberAttribute() {
		if(!$this->providerInfo) {
			return '';
		}
		return $this->providerInfo->npi_number;
	}
	public function setNpiNumberAttribute($value) {
		if(!$this->providerInfo) {
			return '';
		}
		$this->providerInfo->npi_number = $value;
		$this->providerInfo->save();
	}

	// qualification
	public function getQualificationAttribute() {
		if(!$this->providerInfo) {
			return '';
		}
		return $this->providerInfo->qualification;
	}
	public function setQualificationAttribute($value) {
		if(!$this->providerInfo) {
			return '';
		}
		$this->providerInfo->qualification = $value;
		$this->providerInfo->save();
	}

	/*
	// status
	public function getStatusAttribute() {
		return $this->getUserConfigByKey('status');
	}
	public function setStatusAttribute($value) {
		return $this->setUserConfigByKey('status', $value);
	}
	*/

	// daily_reminder_optin
	public function getDailyReminderOptinAttribute() {
		if(!$this->patientInfo) return '';
		return $this->patientInfo->daily_reminder_optin;
	}
	public function setDailyReminderOptinAttribute($value) {
		if(!$this->patientInfo) return '';
		$this->patientInfo->daily_reminder_optin = $value;
		$this->patientInfo->save();
		return true;
	}

	// daily_reminder_time
	public function getDailyReminderTimeAttribute() {
		if(!$this->patientInfo) return '';
		return $this->patientInfo->daily_reminder_time;
	}
	public function setDailyReminderTimeAttribute($value) {
		if(!$this->patientInfo) return '';
		$this->patientInfo->daily_reminder_time = $value;
		$this->patientInfo->save();
		return true;
	}

	// daily_reminder_areas
	public function getDailyReminderAreasAttribute() {
		if(!$this->patientInfo) return '';
		return $this->patientInfo->daily_reminder_areas;
	}
	public function setDailyReminderAreasAttribute($value) {
		if(!$this->patientInfo) return '';
		$this->patientInfo->daily_reminder_areas = $value;
		$this->patientInfo->save();
		return true;
	}

	// hospital_reminder_optin
	public function getHospitalReminderOptinAttribute() {
		if(!$this->patientInfo) return '';
		return $this->patientInfo->hospital_reminder_optin;
	}
	public function setHospitalReminderOptinAttribute($value) {
		if(!$this->patientInfo) return '';
		$this->patientInfo->hospital_reminder_optin = $value;
		$this->patientInfo->save();
		return true;
	}

	// hospital_reminder_time
	public function getHospitalReminderTimeAttribute() {
		if(!$this->patientInfo) return '';
		return $this->patientInfo->hospital_reminder_time;
	}
	public function setHospitalReminderTimeAttribute($value) {
		if(!$this->patientInfo) return '';
		$this->patientInfo->hospital_reminder_time = $value;
		$this->patientInfo->save();
		return true;
	}

	// hospital_reminder_areas
	public function getHospitalReminderAreasAttribute() {
		if(!$this->patientInfo) return '';
		return $this->patientInfo->hospital_reminder_areas;
	}
	public function setHospitalReminderAreasAttribute($value) {
		if(!$this->patientInfo) return '';
		$this->patientInfo->hospital_reminder_areas = $value;
		$this->patientInfo->save();
		return true;
	}

	/*
	// address
	public function getAddressAttribute() {
		return $this->getUserConfigByKey('address');
	}
	public function setAddressAttribute($value) {
		return $this->setUserConfigByKey('address', $value);
	}

	// address2
	public function getAddress2Attribute() {
		return $this->getUserConfigByKey('address2');
	}
	public function setAddress2Attribute($value) {
		return $this->setUserConfigByKey('address2', $value);
	}
	*/

	/*
	// city
	public function getCityAttribute() {
		return $this->getUserConfigByKey('city');
	}
	public function setCityAttribute($value) {
		return $this->setUserConfigByKey('city', $value);
	}

	// state
	public function getStateAttribute() {
		return $this->getUserConfigByKey('state');
	}
	public function setStateAttribute($value) {
		return $this->setUserConfigByKey('state', $value);
	}

	// zip
	public function getZipAttribute() {
		return $this->getUserConfigByKey('zip');
	}
	public function setZipAttribute($value) {
		return $this->setUserConfigByKey('zip', $value);
	}
	*/

	// phone (study_phone_nmber)
	public function getPrimaryPhoneAttribute() {
		$phoneNumber = $this->phoneNumbers()->where('is_primary', 1)->first();
		if($phoneNumber) {
			return $phoneNumber->number;
		} else {
			return '';
		}
	}

	public function getHomePhoneNumberAttribute() {
		return $this->getPhoneAttribute();
	}
	public function getPhoneAttribute() {
		$phoneNumber = $this->phoneNumbers()->where('type', 'home')->first();
		if($phoneNumber) {
			return $phoneNumber->number;
		} else {
			return '';
		}
	}
	public function setHomePhoneNumberAttribute($value) {
		return $this->setPhoneAttribute($value);
	}
	public function setPhoneAttribute($value) {
		$phoneNumber = $this->phoneNumbers()->where('type', 'home')->first();
		if($phoneNumber) {
			$phoneNumber->number = $value;
		} else {
			$phoneNumber = new PhoneNumber();
			$phoneNumber->user_id = $this->ID;
			$phoneNumber->is_primary = 1;
			$phoneNumber->number = $value;
			$phoneNumber->type = 'home';
		}
		$phoneNumber->save();
		return true;
	}

	/*
	// home_phone_number
	public function getHomePhoneNumberAttribute() {
		return $this->getUserConfigByKey('home_phone_number');
	}

	public function setHomePhoneNumberAttribute($value) {
		return $this->setUserConfigByKey('home_phone_number', $value);
	}
	*/

	// work_phone_number
	public function getWorkPhoneNumberAttribute() {
		$phoneNumber = $this->phoneNumbers()->where('type', 'work')->first();
		if($phoneNumber) {
			return $phoneNumber->number;
		} else {
			return '';
		}
	}

	public function setWorkPhoneNumberAttribute($value) {
		$phoneNumber = $this->phoneNumbers()->where('type', 'work')->first();
		if($phoneNumber) {
			$phoneNumber->number = $value;
		} else {
			$phoneNumber = new PhoneNumber();
			$phoneNumber->user_id = $this->ID;
			$phoneNumber->number = $value;
			$phoneNumber->type = 'work';
		}
		$phoneNumber->save();
		return true;
	}

	// mobile_phone_number
	public function getMobilePhoneNumberAttribute() {
		$phoneNumber = $this->phoneNumbers()->where('type', 'mobile')->first();
		if($phoneNumber) {
			return $phoneNumber->number;
		} else {
			return '';
		}
	}

	public function setMobilePhoneNumberAttribute($value) {
		$phoneNumber = $this->phoneNumbers()->where('type', 'mobile')->first();
		if($phoneNumber) {
			$phoneNumber->number = $value;
		} else {
			$phoneNumber = new PhoneNumber();
			$phoneNumber->user_id = $this->ID;
			$phoneNumber->number = $value;
			$phoneNumber->type = 'mobile';
		}
		$phoneNumber->save();
		return true;
	}


	// birth date
	public function getBirthDateAttribute() {
		if(!$this->patientInfo) return '';
		return $this->patientInfo->birth_date;
	}

	public function setBirthDateAttribute($value) {
		if(!$this->patientInfo) return '';
		$this->patientInfo->birth_date = $value;
		$this->patientInfo->save();
		return true;
	}

	// gender
	public function getGenderAttribute() {
		if(!$this->patientInfo) return '';
		return $this->patientInfo->gender;
	}
	public function setGenderAttribute($value) {
		if(!$this->patientInfo) return '';
		$this->patientInfo->gender = $value;
		$this->patientInfo->save();
		return true;
	}

	// email
	public function setEmailAttribute($value) {
		return $this->user_email = $value;
	}

	public function getAgeAttribute() {
		$from = new DateTime($this->birthDate);
		$to   = new DateTime('today');
		return $from->diff($to)->y;
	}

	// cur_month_activity_time
	public function getCurMonthActivityTimeAttribute() {
		return $this->monthlyTime;
	}
	public function getMonthlyTimeAttribute() {
		if(!$this->patientInfo) return '';
		return $this->patientInfo->cur_month_activity_time;
	}
	public function setCurMonthActivityTimeAttribute($value) {
		if(!$this->patientInfo) return '';
		$this->patientInfo->cur_month_activity_time = $value;
		$this->patientInfo->save();
	}

	// timezone
	public function getPreferredContactTimeZoneAttribute() {
		return $this->getTimeZoneAttribute();
	}
	public function getTimeZoneAttribute() {
		if(!$this->patientInfo) return '';
		return $this->patientInfo->preferred_contact_timezone;
	}
	public function setPreferredContactTimeZoneAttribute($value){
		return $this->setTimeZoneAttribute($value);
	}
	public function setTimeZoneAttribute($value) {
		if(!$this->patientInfo) return '';
		$this->patientInfo->preferred_contact_timezone = $value;
		$this->patientInfo->save();
		return true;
	}

	// preferred_contact_time
	public function getPreferredContactTimeAttribute() {
		if(!$this->patientInfo) return '';
		return $this->patientInfo->preferred_contact_time;
	}
	public function setPreferredContactTimeAttribute($value) {
		if(!$this->patientInfo) return '';
		$this->patientInfo->preferred_contact_time = $value;
		$this->patientInfo->save();
		return true;
	}

	// preferred_contact_method
	public function getPreferredContactMethodAttribute() {
		if(!$this->patientInfo) return '';
		return $this->patientInfo->preferred_contact_method;
	}
	public function setPreferredContactMethodAttribute($value) {
		if(!$this->patientInfo) return '';
		$this->patientInfo->preferred_contact_method = $value;
		$this->patientInfo->save();
		return true;
	}

	// preferred_contact_language
	public function getPreferredContactLanguageAttribute() {
		if(!$this->patientInfo) return '';
		return $this->patientInfo->preferred_contact_language;
	}
	public function setPreferredContactLanguageAttribute($value) {
		if(!$this->patientInfo) return '';
		$this->patientInfo->preferred_contact_language = $value;
		$this->patientInfo->save();
		return true;
	}

	// mrn_number
	public function getMrnNumberAttribute() {
		return $this->getMRNAttribute();
	}
	public function getMRNAttribute() {
		if(!$this->patientInfo) return '';
		return $this->patientInfo->mrn_number;
	}
	public function setMrnNumberAttribute($value) {
		return $this->setMRNAttribute($value);
	}
	public function setMRNAttribute($value) {
		if(!$this->patientInfo) return '';
		$this->patientInfo->mrn_number = $value;
		$this->patientInfo->save();
		return true;
	}

	// care_team
	public function getCareTeamAttribute() {
		$ct = array();
		$careTeamMembers = $this->patientCareTeamMembers()->groupBy('member_user_id')->get();
		if ($careTeamMembers->count() > 0) {
			foreach($careTeamMembers as $careTeamMember) {
				$ct[] = $careTeamMember->member_user_id;
			}
		}
		return $ct;
	}
	public function setCareTeamAttribute($memberUserIds) {
		if(!is_array($memberUserIds)) {
			$this->patientCareTeamMembers()->where('type', 'member')->delete();
			return false; // must be array
		}
		$this->patientCareTeamMembers()->where('type', 'member')->whereNotIn('member_user_id', $memberUserIds)->delete();
		foreach($memberUserIds as $memberUserId) {
			$careTeamMember = $this->patientCareTeamMembers()->where('type', 'member')->where('member_user_id', $memberUserId)->first();
			if($careTeamMember) {
				$careTeamMember->member_user_id = $memberUserId;
			} else {
				$careTeamMember = new PatientCareTeamMember();
				$careTeamMember->user_id = $this->ID;
				$careTeamMember->member_user_id = $memberUserId;
				$careTeamMember->type = 'member';
			}
			$careTeamMember->save();
		}
		return true;
	}

	// send_alert_to
	public function getSendAlertToAttribute() {
		$ctmsa = array();
		$careTeamMembers = $this->patientCareTeamMembers()->get();
		if ($careTeamMembers->count() > 0) {
			foreach($careTeamMembers as $careTeamMember) {
				if($careTeamMember->type == 'send_alert_to') {
					$ctmsa[] = $careTeamMember->member_user_id;
				}
			}
		}
		return $ctmsa;
	}
	public function setSendAlertToAttribute($memberUserIds) {
		if(!is_array($memberUserIds)) {
			$this->patientCareTeamMembers()->where('type', 'send_alert_to')->delete();
			return false; // must be array
		}
		$this->patientCareTeamMembers()->where('type', 'send_alert_to')->whereNotIn('member_user_id', $memberUserIds)->delete();
		foreach($memberUserIds as $memberUserId) {
			$careTeamMember = $this->patientCareTeamMembers()->where('type', 'send_alert_to')->where('member_user_id', $memberUserId)->first();
			if($careTeamMember) {
				$careTeamMember->member_user_id = $memberUserId;
			} else {
				$careTeamMember = new PatientCareTeamMember();
				$careTeamMember->user_id = $this->ID;
				$careTeamMember->member_user_id = $memberUserId;
				$careTeamMember->type = 'send_alert_to';
			}
			$careTeamMember->save();
		}
		return true;
	}

	// billing_provider
	public function getBillingProviderIDAttribute() {
		$bp = '';
		$careTeamMembers = $this->patientCareTeamMembers()->get();
		if ($careTeamMembers->count() > 0) {
			foreach($careTeamMembers as $careTeamMember) {
				if($careTeamMember->type == 'billing_provider') {
					$bp = $careTeamMember->member_user_id;
				}
			}
		}
		return $bp;
	}
	public function setBillingProviderIDAttribute($value) {
		if(empty($value)) {
			$this->patientCareTeamMembers()->where('type', 'billing_provider')->delete();
			return true;
		}
		$careTeamMember = $this->patientCareTeamMembers()->where('type', 'billing_provider')->first();
		if($careTeamMember) {
			$careTeamMember->member_user_id = $value;
		} else {
			$careTeamMember = new PatientCareTeamMember();
			$careTeamMember->user_id = $this->ID;
			$careTeamMember->member_user_id = $value;
			$careTeamMember->type = 'billing_provider';
		}
		$careTeamMember->save();
		return true;
	}

	// lead_contact
	public function getLeadContactIDAttribute() {
		$lc = array();
		$careTeamMembers = $this->patientCareTeamMembers()->get();
		if ($careTeamMembers->count() > 0) {
			foreach($careTeamMembers as $careTeamMember) {
				if($careTeamMember->type == 'lead_contact') {
					$lc = $careTeamMember->member_user_id;
				}
			}
		}
		return $lc;
	}
	public function setLeadContactIDAttribute($value) {
		if(empty($value)) {
			$this->patientCareTeamMembers()->where('type', 'lead_contact')->delete();
			return true;
		}
		$careTeamMember = $this->patientCareTeamMembers()->where('type', 'lead_contact')->first();
		if($careTeamMember) {
			$careTeamMember->member_user_id = $value;
		} else {
			$careTeamMember = new PatientCareTeamMember();
			$careTeamMember->user_id = $this->ID;
			$careTeamMember->member_user_id = $value;
			$careTeamMember->type = 'lead_contact';
		}
		$careTeamMember->save();
		return true;
	}

	// preferred_contact_location
	public function getPreferredLocationAddress() {
		if(!$this->patientInfo) return '';
		$locationId = $this->patientInfo->preferred_contact_location;
		if(empty($locationId)) {
			return false;
		}
		$location = Location::find($locationId);
		return $location;
	}
	public function getPreferredLocationName() {
		if(!$this->patientInfo) return '';
		$locationId = $this->patientInfo->preferred_contact_location;
		if(empty($locationId)) {
			return false;
		}
		$location = Location::find($locationId);
		return (isset($location->name)) ?
			$location->name :
			'';
	}
	public function getPreferredContactLocationAttribute() {
		if(!$this->patientInfo) return '';
		return $this->patientInfo->preferred_contact_location;
	}
	public function setPreferredContactLocationAttribute($value) {
		if(!$this->patientInfo) return '';
		$this->patientInfo->preferred_contact_location = $value;
		$this->patientInfo->save();
		return true;
	}

	// prefix
	public function getPrefixAttribute() {
		if(!$this->providerInfo) {
			return '';
		}
		return $this->providerInfo->prefix;
	}
	public function setPrefixAttribute($value) {
		if(!$this->providerInfo) {
			return '';
		}
		$this->providerInfo->prefix = $value;
		$this->providerInfo->save();
	}

	// consent_date
	public function getConsentDateAttribute() {
		if(!$this->patientInfo) return '';
		return $this->patientInfo->consent_date;
	}
	public function setConsentDateAttribute($value) {
		if(!$this->patientInfo) return '';
		$this->patientInfo->consent_date = $value;
		$this->patientInfo->save();
		return true;
	}

	// agent_name
	public function getAgentNameAttribute() {
		if(!$this->patientInfo) return '';
		return $this->patientInfo->agent_name;
	}
	public function setAgentNameAttribute($value) {
		if(!$this->patientInfo) return '';
		$this->patientInfo->agent_name = $value;
		$this->patientInfo->save();
		return true;
	}

	// agent_phone
	public function getAgentTelephoneAttribute() {
		return $this->getAgentPhoneAttribute();
	}
	public function getAgentPhoneAttribute() {
		if(!$this->patientInfo) return '';
		return $this->patientInfo->agent_telephone;
	}
	public function setAgentTelephoneAttribute($value) {
		return $this->setAgentPhoneAttribute($value);
	}
	public function setAgentPhoneAttribute($value) {
		if(!$this->patientInfo) return '';
		$this->patientInfo->agent_telephone = $value;
		$this->patientInfo->save();
		return true;
	}

	// agent_email
	public function getAgentEmailAttribute() {
		if(!$this->patientInfo) return '';
		return $this->patientInfo->agent_email;
	}
	public function setAgentEmailAttribute($value) {
		if(!$this->patientInfo) return '';
		$this->patientInfo->agent_email = $value;
		$this->patientInfo->save();
		return true;
	}

	// agent_relationship
	public function getAgentRelationshipAttribute() {
		if(!$this->patientInfo) return '';
		return $this->patientInfo->agent_relationship;
	}
	public function setAgentRelationshipAttribute($value) {
		if(!$this->patientInfo) return '';
		$this->patientInfo->agent_relationship = $value;
		$this->patientInfo->save();
		return true;
	}

	public function getCarePlanQAApproverAttribute() {
		$meta = $this->meta->where('meta_key', 'careplan_qa_approver')->lists('meta_value');
		if(!empty($meta)) {
			return $meta[0];
		}
		return 0;
	}

	public function setCarePlanQAApproverAttribute($value) {
		$meta = $this->meta->where('meta_key', 'careplan_qa_approver')->first();
		if(!empty($meta)) {
			$meta->meta_value = $value;
			$meta->save();
		} else {
			$userMeta = $this->meta()->firstOrNew([
				'meta_key' => 'careplan_qa_approver',
				'meta_value' => $value,
				'user_id' => $this->ID
			]);
			$this->meta()->save($userMeta);
		}
		return true;
	}

	public function getCarePlanQADateAttribute() {
		$meta = $this->meta->where('meta_key', 'careplan_qa_date')->lists('meta_value');
		if(!empty($meta)) {
			return $meta[0];
		}
		return '';
	}

	public function setCarePlanQADateAttribute($value) {
		$meta = $this->meta->where('meta_key', 'careplan_qa_date')->first();
		if(!empty($meta)) {
			$meta->meta_value = $value;
			$meta->save();
		} else {
			$userMeta = $this->meta()->firstOrNew([
				'meta_key' => 'careplan_qa_date',
				'meta_value' => $value,
				'user_id' => $this->ID
			]);
			$this->meta()->save($userMeta);
		}
		return true;
	}

	public function getCarePlanProviderApproverAttribute() {
		$meta = $this->meta->where('meta_key', 'careplan_provider_approver')->lists('meta_value');
		if(!empty($meta)) {
			return $meta[0];
		}
		return '';
	}

	public function setCarePlanProviderApproverAttribute($value) {
		$meta = $this->meta->where('meta_key', 'careplan_provider_approver')->first();
		if(!empty($meta)) {
			$meta->meta_value = $value;
			$meta->save();
		} else {
			$userMeta = $this->meta()->firstOrNew([
				'meta_key' => 'careplan_provider_approver',
				'meta_value' => $value,
				'user_id' => $this->ID
			]);
			$this->meta()->save($userMeta);
		}
		return true;
	}

	public function getCarePlanProviderApproverDateAttribute() {
		$meta = $this->meta->where('meta_key', 'careplan_provider_date')->lists('meta_value');
		if(!empty($meta)) {
			return $meta[0];
		}
		return '';
	}

	public function setCarePlanProviderApproverDateAttribute($value) {
		$meta = $this->meta->where('meta_key', 'careplan_provider_date')->first();
		if(!empty($meta)) {
			$meta->meta_value = $value;
			$meta->save();
		} else {
			$userMeta = $this->meta()->firstOrNew([
				'meta_key' => 'careplan_provider_date',
				'meta_value' => $value,
				'user_id' => $this->ID
			]);
			$this->meta()->save($userMeta);
		}
		return true;
	}

	public function getCarePlanStatusAttribute() {
		$meta = $this->meta->where('meta_key', 'careplan_status')->lists('meta_value');
		if(!empty($meta)) {
			return $meta[0];
		}
		return '';
	}

	public function setCarePlanStatusAttribute($value) {
		$meta = $this->meta->where('meta_key', 'careplan_status')->first();
		if(!empty($meta)) {
			$meta->meta_value = $value;
			$meta->save();
		} else {
			$meta = $this->meta()->firstOrNew([
				'meta_key' => 'careplan_status',
				'meta_value' => $value,
				'user_id' => $this->ID
			]);
			$this->meta()->save($meta);
		}
		return true;
	}

	public function getCcmStatusAttribute() {
		if(!$this->patientInfo) return '';
		return $this->patientInfo->ccm_status;
	}

	public function setCcmStatusAttribute($value) {
		if(!$this->patientInfo) return '';
		$statusBefore = $this->patientInfo->ccm_status;
		$this->patientInfo->ccm_status = $value;
		$this->patientInfo->save();
		// update date tracking
		if( $statusBefore !== $value ) {
			if ($value == 'paused') {
				$this->datePaused = date("Y-m-d H:i:s");
			};
			if ($value == 'withdrawn') {
				$this->dateWithdrawn = date("Y-m-d H:i:s");
			};
		}
		return true;
	}

	public function getDatePausedAttribute() {
		if(!$this->patientInfo) return '';
		return $this->patientInfo->date_paused;
	}

	public function setDatePausedAttribute($value) {
		if(!$this->patientInfo) return '';
		$this->patientInfo->date_paused = $value;
		$this->patientInfo->save();
		return true;
	}

	public function getDateWithdrawnAttribute() {
		if(!$this->patientInfo) return '';
		return $this->patientInfo->date_withdrawn;
	}

	public function setDateWithdrawnAttribute($value) {
		if(!$this->patientInfo) return '';
		$this->patientInfo->date_withdrawn = $value;
		$this->patientInfo->save();
		return true;
	}

	// Whenever the user_pass field is modified, WordPress' internal hashing function will run
	public function setUserPassAttribute($pass)
	{
		$this->attributes['user_pass'] = WpPassword::make($pass);
	}
	// END ATTRIBUTES






	// MISC, these should be removed eventually

	public function role($blogId = false)
	{
		if(!$blogId) {
			$blogId = $this->blogId();
		}
		$role = UserMeta::select('meta_value')->where('user_id', $this->ID)->where('meta_key','wp_'.$blogId.'_capabilities')->first();
		if(!$role) {
			return false;
		} else {
			$data = unserialize($role['meta_value']);
			return key($data);
		}
	}

	public function blogId(){
		return $this->program_id;
	}

	public function primaryProgram() {
		return $this->belongsTo('App\WpBlog', 'program_id', 'blog_id');
	}

	public function programs() {
		return $this->belongsToMany('App\WpBlog', 'lv_program_user', 'user_id', 'program_id');
	}

	// user data scrambler
	public function scramble($randomUserInfo = false) {
		// states array
		$states = array('Alabama' => 'AL', 'Alaska' => 'AK', 'Arizona' => 'AZ', 'Arkansas' => 'AR', 'California' => 'CA', 'Colorado' => 'CO', 'Connecticut' => 'CT', 'Delaware' => 'DE', 'Florida' => 'FL', 'Georgia' => 'GA', 'Hawaii' => 'HI', 'Idaho' => 'ID', 'Illinois' => 'IL', 'Indiana' => 'IN', 'Iowa' => 'IA', 'Kansas' => 'KS', 'Kentucky' => 'KY', 'Louisiana' => 'LA', 'Maine' => 'ME', 'Maryland' => 'MD', 'Massachusetts' => 'MA', 'Michigan' => 'MI', 'Minnesota' => 'MN', 'Mississippi' => 'MS', 'Missouri' => 'MO', 'Montana' => 'MT', 'Nebraska' => 'NE', 'Nevada' => 'NV', 'New Hampshire' => 'NH', 'New Jersey' => 'NJ', 'New Mexico' => 'NM', 'New York' => 'NY', 'North Carolina' => 'NC', 'North Dakota' => 'ND', 'Ohio' => 'OH', 'Oklahoma' => 'OK', 'Oregon' => 'OR', 'Pennsylvania' => 'PA', 'Rhode Island' => 'RI', 'South Carolina' => 'SC', 'South Dakota' => 'SD', 'Tennessee' => 'TN', 'Texas' => 'TX', 'Utah' => 'UT', 'Vermont' => 'VT', 'Virginia' => 'VA', 'Washington' => 'WA', 'West Virginia' => 'WV', 'Wisconsin' => 'WI', 'Wyoming' => 'WY');

		// Some Randomness
		// https://randomuser.me/api/?nat=us&results=3
		if(!$randomUserInfo) {
			$json_string = file_get_contents("https://randomuser.me/api/?nat=us&results=1");
			if (empty($json_string)) {
				return false;
			}
			$randomUserInfo = json_decode($json_string);
			$randomUserInfo = $randomUserInfo->results[0];
		}

		//dd($randomUserInfo);
		// set random data
		$user = $this;
		$user->first_name = $randomUserInfo->name->first;
		$user->user_nicename = $randomUserInfo->name->first;
		$user->last_name = 'Z-'.$randomUserInfo->name->last;
		$user->user_login = $randomUserInfo->login->username;
		$user->user_pass = $randomUserInfo->login->password;
		$user->user_email = $randomUserInfo->email;
		//$user->display_name = $randomUserInfo->username;
		$user->MRN = rand();
		$user->gender = 'M';
		$user->address = $randomUserInfo->location->street;
		$user->city = $randomUserInfo->location->city;
		$user->state = $randomUserInfo->location->state;
		$user->zip = $randomUserInfo->location->postcode;
		$user->phone = '111-234-5678';
		$user->workPhoneNumber = '222-234-5678';
		$user->mobilePhoneNumber = '333-234-5678';
		$user->birthDate = date('Y-m-d', $randomUserInfo->dob);
		$user->agentName = 'Secret Agent';
		$user->agentPhone = '111-234-5678';
		$user->agentEmail = 'secret@agent.net';
		$user->agentRelationship = 'SA';
		$user->save();
	}

	public function createNewUser($user_email, $user_pass) {
		$this->user_login = $user_email;
		$this->user_email = $user_email;
		$this->user_pass = $user_pass;
		$this->save();

		return $this;
	}


	public function getUCP() {
		$userUcp = $this->ucp()->with(['item.meta', 'item.question'])->get();
		$userUcpData = array('ucp' => array(), 'obs_keys' => array(), 'alert_keys' => array());
		if($userUcp->count() > 0) {
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
}

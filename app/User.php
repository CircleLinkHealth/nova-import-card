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
	protected $fillable = ['user_login', 'user_pass', 'user_nicename', 'user_email', 'user_url', 'user_registered', 'user_activation_log', 'user_status', 'auto_attach_programs', 'display_name', 'spam'];

	protected $hidden = ['user_pass'];

	protected $dates = ['deleted','user_registered'];

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
		"study_phone_number" => "required",
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
		// hack overrides and depreciated keys, @todo fix these
		if($attribute == 'careplanProviderDate') {
			$attribute = 'careplanProviderApproverDate';
		} else if($attribute == 'mrnNumber') {
			$attribute = 'mrn';
		} else if($attribute == 'studyPhoneNumber') {
			$attribute = 'phone';
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

	// first_name
    public function getFirstNameAttribute() {
		return $this->getUserMetaByKey('first_name');
	}
	public function setFirstNameAttribute($value) {
		$this->setUserMetaByKey('first_name', $value);
		$this->display_name = $this->fullName;
		$this->save();
		return true;
	}

	// last_name
    public function getLastNameAttribute() {
		return $this->getUserMetaByKey('last_name');
	}
	public function setLastNameAttribute($value) {
		$this->setUserMetaByKey('last_name', $value);
		$this->display_name = $this->fullName;
		$this->save();
		return true;
	}

	// full name
	public function getFullNameAttribute() {
		$firstName = $this->firstName;
		$lastName = $this->lastName;
		return $firstName . ' ' . $lastName;
	}

	// full name w/ id
	public function getFullNameWithIdAttribute() {
		$name = $this->fullName;
		return $name . ' ('.$this->ID.')';
	}

	// preferred_cc_contact_days
	public function getPreferredCcContactDaysAttribute() {
		return $this->getUserConfigByKey('preferred_cc_contact_days');
	}
	public function setPreferredCcContactDaysAttribute($value) {
		return $this->setUserConfigByKey('preferred_cc_contact_days', $value);
	}

	// active_date
	public function getActiveDateAttribute() {
		return $this->getUserConfigByKey('active_date');
	}
	public function setActiveDateAttribute($value) {
		return $this->setUserConfigByKey('active_date', $value);
	}

	// registration_date
	public function getRegistrationDateAttribute() {
		return $this->getUserConfigByKey('registration_date');
	}
	public function setRegistrationDateAttribute($value) {
		return $this->setUserConfigByKey('registration_date', $value);
	}

	// specialty
	public function getSpecialtyAttribute() {
		return $this->getUserConfigByKey('specialty');
	}
	public function setSpecialtyAttribute($value) {
		return $this->setUserConfigByKey('specialty', $value);
	}

	// npi_number
	public function getNpiNumberAttribute() {
		return $this->getUserConfigByKey('npi_number');
	}
	public function setNpiNumberAttribute($value) {
		return $this->setUserConfigByKey('npi_number', $value);
	}

	// qualification
	public function getQualificationAttribute() {
		return $this->getUserConfigByKey('qualification');
	}
	public function setQualificationAttribute($value) {
		return $this->setUserConfigByKey('qualification', $value);
	}

	// status
	public function getStatusAttribute() {
		return $this->getUserConfigByKey('status');
	}
	public function setStatusAttribute($value) {
		return $this->setUserConfigByKey('status', $value);
	}

	// daily_reminder_optin
	public function getDailyReminderOptinAttribute() {
		return $this->getUserConfigByKey('daily_reminder_optin');
	}
	public function setDailyReminderOptinAttribute($value) {
		return $this->setUserConfigByKey('daily_reminder_optin', $value);
	}

	// daily_reminder_time
	public function getDailyReminderTimeAttribute() {
		return $this->getUserConfigByKey('daily_reminder_time');
	}
	public function setDailyReminderTimeAttribute($value) {
		return $this->setUserConfigByKey('daily_reminder_time', $value);
	}

	// daily_reminder_areas
	public function getDailyReminderAreasAttribute() {
		return $this->getUserConfigByKey('daily_reminder_areas');
	}
	public function setDailyReminderAreasAttribute($value) {
		return $this->setUserConfigByKey('daily_reminder_areas', $value);
	}

	// hospital_reminder_optin
	public function getHospitalReminderOptinAttribute() {
		return $this->getUserConfigByKey('hospital_reminder_optin');
	}
	public function setHospitalReminderOptinAttribute($value) {
		return $this->setUserConfigByKey('hospital_reminder_optin', $value);
	}

	// hospital_reminder_time
	public function getHospitalReminderTimeAttribute() {
		return $this->getUserConfigByKey('hospital_reminder_time');
	}
	public function setHospitalReminderTimeAttribute($value) {
		return $this->setUserConfigByKey('hospital_reminder_time', $value);
	}

	// hospital_reminder_areas
	public function getHospitalReminderAreasAttribute() {
		return $this->getUserConfigByKey('hospital_reminder_areas');
	}
	public function setHospitalReminderAreasAttribute($value) {
		return $this->setUserConfigByKey('hospital_reminder_areas', $value);
	}

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

	// phone (study_phone_nmber)
	public function getPhoneAttribute() {
		return $this->getUserConfigByKey('study_phone_number');
	}
	public function setPhoneAttribute($value) {
		return $this->setUserConfigByKey('study_phone_number', $value);
	}

	// home_phone_number
	public function getHomePhoneNumberAttribute() {
		return $this->getUserConfigByKey('home_phone_number');
	}

	public function setHomePhoneNumberAttribute($value) {
		return $this->setUserConfigByKey('home_phone_number', $value);
	}

	// work_phone_number
	public function getWorkPhoneNumberAttribute() {
		return $this->getUserConfigByKey('work_phone_number');
	}

	public function setWorkPhoneNumberAttribute($value) {
		return $this->setUserConfigByKey('work_phone_number', $value);
	}

	// mobile_phone_number
	public function getMobilePhoneNumberAttribute() {
		return $this->getUserConfigByKey('mobile_phone_number');
	}

	public function setMobilePhoneNumberAttribute($value) {
		return $this->setUserConfigByKey('mobile_phone_number', $value);
	}


	// birth date
	public function getBirthDateAttribute() {
		return $this->getUserConfigByKey('birth_date');
	}
	public function setBirthDateAttribute($value) {
		return $this->setUserConfigByKey('birth_date', $value);
	}

	// gender
	public function getGenderAttribute() {
		return $this->getUserConfigByKey('gender');
	}
	public function setGenderAttribute($value) {
		return $this->setUserConfigByKey('gender', $value);
	}

	// email
	public function setEmailAttribute($value) {
		return $this->setUserConfigByKey('email', $value);
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
		$time = $this->meta->where('meta_key', 'cur_month_activity_time')->lists('meta_value');
		if(!empty($time)) {
			return $time[0];
		} else {
			return 0;
		}
	}
	public function setCurMonthActivityTimeAttribute($value) {
		return $this->setUserConfigByKey('cur_month_activity_time', $value);
	}

	// timezone
	public function getPreferredContactTimeZoneAttribute() {
		return $this->getTimeZoneAttribute();
	}
	public function getTimeZoneAttribute() {
		return $this->getUserConfigByKey('preferred_contact_timezone');
	}
	public function setPreferredContactTimeZoneAttribute($value){
		return $this->setTimeZoneAttribute($value);
	}
	public function setTimeZoneAttribute($value) {
		return $this->setUserConfigByKey('preferred_contact_timezone', $value);
	}

	// preferred_contact_time
	public function getPreferredContactTimeAttribute() {
		return $this->getUserConfigByKey('preferred_contact_time');
	}
	public function setPreferredContactTimeAttribute($value) {
		return $this->setUserConfigByKey('preferred_contact_time', $value);
	}

	// preferred_contact_method
	public function getPreferredContactMethodAttribute() {
		return $this->getUserConfigByKey('preferred_contact_method');
	}
	public function setPreferredContactMethodAttribute($value) {
		return $this->setUserConfigByKey('preferred_contact_method', $value);
	}

	// preferred_contact_language
	public function getPreferredContactLanguageAttribute() {
		return $this->getUserConfigByKey('preferred_contact_language');
	}
	public function setPreferredContactLanguageAttribute($value) {
		return $this->setUserConfigByKey('preferred_contact_language', $value);
	}

	// mrn_number
	public function getMRNAttribute() {
		return $this->getUserConfigByKey('mrn_number');
	}
	public function setMRNAttribute($value) {
		return $this->setUserConfigByKey('mrn_number', $value);
	}

	// care_team
	public function getCareTeamAttribute() {
		return $this->getUserConfigByKey('care_team');
	}
	public function setCareTeamAttribute($value) {
		return $this->setUserConfigByKey('care_team', $value);
	}

	// send_alert_to
	public function getSendAlertToAttribute() {
		return $this->getUserConfigByKey('send_alert_to');
	}
	public function setSendAlertToAttribute($value) {
		return $this->setUserConfigByKey('send_alert_to', $value);
	}

	// billing_provider
	public function getBillingProviderIDAttribute() {
		return $this->getUserConfigByKey('billing_provider');
	}
	public function setBillingProviderIDAttribute($value) {
		return $this->setUserConfigByKey('billing_provider', $value);
	}

	// lead_contact
	public function getLeadContactIDAttribute() {
		return $this->getUserConfigByKey('lead_contact');
	}
	public function setLeadContactIDAttribute($value) {
		return $this->setUserConfigByKey('lead_contact', $value);
	}

	// preferred_contact_location
	public function getPreferredLocationName() {
		$locationId = $this->getUserConfigByKey('preferred_contact_location');
		if(empty($locationId)) {
			return false;
		}
		$location = Location::find($locationId);
		return (isset($location->name)) ?
			$location->name :
			'';
	}
	public function getpreferredContactLocationAttribute() {
		return $this->getUserConfigByKey('preferred_contact_location');
	}
	public function setpreferredContactLocationAttribute($value) {
		return $this->setUserConfigByKey('preferred_contact_location', $value);
	}

	// prefix
	public function getPrefixAttribute() {
		return $this->getUserConfigByKey('prefix');
	}
	public function setPrefixAttribute($value) {
		return $this->setUserConfigByKey('prefix', $value);
	}

	// consent_date
	public function getConsentDateAttribute() {
		return $this->getUserConfigByKey('consent_date');
	}
	public function setConsentDateAttribute($value) {
		return $this->setUserConfigByKey('consent_date', $value);
	}

	public function getCarePlanQAApproverAttribute() {
		$meta = $this->meta->where('meta_key', 'careplan_qa_approver')->lists('meta_value');
		if(!empty($meta)) {
			return $meta[0];
		}
		return 0;
	}

	// agent_name
	public function getAgentNameAttribute() {
		return $this->getUserConfigByKey('agent_name');
	}
	public function setAgentNameAttribute($value) {
		return $this->setUserConfigByKey('agent_name', $value);
	}

	// agent_phone
	public function getAgentTelephoneAttribute() {
		return $this->getAgentPhoneAttribute();
	}
	public function getAgentPhoneAttribute() {
		return $this->getUserConfigByKey('agent_telephone');
	}
	public function setAgentTelephoneAttribute($value) {
		return $this->setAgentPhoneAttribute($value);
	}
	public function setAgentPhoneAttribute($value) {
		return $this->setUserConfigByKey('agent_telephone', $value);
	}

	// agent_email
	public function getAgentEmailAttribute() {
		return $this->getUserConfigByKey('agent_email');
	}
	public function setAgentEmailAttribute($value) {
		return $this->setUserConfigByKey('agent_email', $value);
	}

	// agent_relationship
	public function getAgentRelationshipAttribute() {
		return $this->getUserConfigByKey('agent_relationship');
	}
	public function setAgentRelationshipAttribute($value) {
		return $this->setUserConfigByKey('agent_relationship', $value);
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
		$meta = $this->meta->where('meta_key', 'ccm_status')->lists('meta_value');
		if(!empty($meta)) {
			return $meta[0];
		}
		return '';
	}

	public function setCcmStatusAttribute($status) {
		$meta = $this->meta()->where('meta_key', 'ccm_status')->first();
		if(empty($meta)) {
			$meta = new UserMeta;
			$meta->meta_key = 'ccm_status';
			$meta->user_id = $this->ID;
		}
		$statusBefore = $meta->meta_value;
		$meta->meta_value = $status;
		$meta->save();
		// update date tracking
		if( $statusBefore !== $status ) {
			if ($status == 'paused') {
				$this->datePaused = date("Y-m-d H:i:s");
			};
			if ($status == 'withdrawn') {
				$this->dateWithdrawn = date("Y-m-d H:i:s");
			};
		}
		return true;
	}

	public function getDatePausedAttribute() {
		$meta = $this->meta->where('meta_key', 'date_paused')->lists('meta_value');
		if(!empty($meta)) {
			return $meta[0];
		}
		return '';
	}

	public function setDatePausedAttribute($value) {
		$meta = $this->meta->where('meta_key', 'date_paused')->first();
		if(!empty($meta)) {
			$meta->meta_value = $value;
			$meta->save();
		} else {
			$userMeta = $this->meta()->firstOrNew([
				'meta_key' => 'date_paused',
				'meta_value' => $value,
				'user_id' => $this->ID
			]);
			$this->meta()->save($userMeta);
		}
		return true;
	}

	public function getDateWithdrawnAttribute() {
		$meta = $this->meta->where('meta_key', 'date_withdrawn')->lists('meta_value');
		if(!empty($meta)) {
			return $meta[0];
		}
		return '';
	}

	public function setDateWithdrawnAttribute($value) {
		$meta = $this->meta->where('meta_key', 'date_withdrawn')->first();
		if(!empty($meta)) {
			$meta->meta_value = $value;
			$meta->save();
		} else {
			$userMeta = $this->meta()->firstOrNew([
				'meta_key' => 'date_withdrawn',
				'meta_value' => $value,
				'user_id' => $this->ID
			]);
			$this->meta()->save($userMeta);
		}
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
			$randomUserInfo = $randomUserInfo->results[0]->user;
		}

		//dd($randomUserInfo);
		// set random data
		$user = $this;
		$user->firstName = $randomUserInfo->name->first;
		$user->user_nicename = $randomUserInfo->name->first;
		$user->lastName = 'Z-'.$randomUserInfo->name->last;
		$user->user_login = $randomUserInfo->username;
		$user->user_pass = $randomUserInfo->password;
		$user->user_email = $randomUserInfo->email;
		//$user->display_name = $randomUserInfo->username;
		$user->MRN = rand();
		$user->gender = 'M';
		$user->address = $randomUserInfo->location->street;
		$user->city = $randomUserInfo->location->city;
		$user->state = $randomUserInfo->location->state;
		$user->zip = $randomUserInfo->location->zip;
		$user->phone = '111-234-5678';
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

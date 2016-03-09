<?php namespace App;

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
	protected $fillable = ['user_login', 'user_pass', 'user_nicename', 'user_email', 'user_url', 'user_registered', 'user_activation_log', 'user_status', 'display_name', 'spam'];

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
		'display_name'         => 'required',
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

    public function meta()
	{
		return $this->hasMany('App\UserMeta', 'user_id', 'ID');
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
		$userConfig = UserMeta::select('meta_value')->where('user_id', $this->ID)->where('meta_key',$key)->first();
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


    // START ATTRIBUTES

    // basic attributes
    public function getFirstNameAttribute() {
		$name = '';
		$firstName = $this->meta()->where('meta_key', '=', 'first_name')->first();
		if( !empty($firstName) && $firstName->meta_value != '' ) {
			$name = $firstName->meta_value;
		}
		return $name;
	}

	public function setFirstNameAttribute($value) {
		$firstName = $this->meta()->where('meta_key', '=', 'first_name')->first();
		if( !empty($firstName) ) {
			$firstName->meta_value = $value;
			$firstName->save();
		}
		return true;
	}

    public function getLastNameAttribute() {
		$name = '';
		$lastName = $this->meta()->where('meta_key', '=', 'last_name')->first();
		if(!empty($lastName && $lastName->meta_value != '' ) ) {
			$name = $lastName->meta_value;
		}
		return $name;
	}

	public function setLastNameAttribute($value) {
		$lastName = $this->meta()->where('meta_key', '=', 'last_name')->first();
		if( !empty($lastName) ) {
			$lastName->meta_value = $value;
			$lastName->save();
		}
		return true;
	}

	public function getFullNameAttribute() {
		$firstName = $this->firstName;
		$lastName = $this->lastName;
		return $firstName . ' ' . $lastName;
	}

	public function getFullNameWithIdAttribute() {
		$name = $this->fullName;
		return $name . ' ('.$this->ID.')';
	}

	public function getAddressAttribute() {
		$userConfig = $this->userConfig();
		return ($userConfig['address']) ? $userConfig['address'] : '';
	}

	public function setAddressAttribute($value) {
		$key = 'wp_'.$this->blogId().'_user_config';
		$userConfig = UserMeta::where('user_id', $this->ID)->where('meta_key',$key)->first();
		$metaValue = unserialize($userConfig['meta_value']);
		$metaValue['address'] = $value;
		$userConfig->meta_value = serialize($metaValue);
		$userConfig->save();
	}

	public function getCityAttribute() {
		$userConfig = $this->userConfig();
		return ($userConfig['city']) ? $userConfig['city'] : '';
	}

	public function setCityAttribute($value) {
		$key = 'wp_'.$this->blogId().'_user_config';
		$userConfig = UserMeta::where('user_id', $this->ID)->where('meta_key',$key)->first();
		$metaValue = unserialize($userConfig['meta_value']);
		$metaValue['city'] = $value;
		$userConfig->meta_value = serialize($metaValue);
		$userConfig->save();
	}

	public function getStateAttribute() {
		$userConfig = $this->userConfig();
		return ($userConfig['state']) ? $userConfig['state'] : '';
	}

	public function setStateAttribute($value) {
		$key = 'wp_'.$this->blogId().'_user_config';
		$userConfig = UserMeta::where('user_id', $this->ID)->where('meta_key',$key)->first();
		$metaValue = unserialize($userConfig['meta_value']);
		$metaValue['state'] = $value;
		$userConfig->meta_value = serialize($metaValue);
		$userConfig->save();
	}

	// zip
	public function getZipAttribute() {
		$userConfig = $this->userConfig();
		return ($userConfig['zip']) ? $userConfig['zip'] : '';
	}

	public function setZipAttribute($value) {
		$key = 'wp_'.$this->blogId().'_user_config';
		$userConfig = UserMeta::where('user_id', $this->ID)->where('meta_key',$key)->first();
		$metaValue = unserialize($userConfig['meta_value']);
		$metaValue['zip'] = $value;
		$userConfig->meta_value = serialize($metaValue);
		$userConfig->save();
	}

	public function getPhoneAttribute() {
		$userConfig = $this->userConfig();
		return ($userConfig['study_phone_number']) ? $userConfig['study_phone_number'] : '';
	}

	public function setPhoneAttribute($value) {
		$key = 'wp_'.$this->blogId().'_user_config';
		$userConfig = UserMeta::where('user_id', $this->ID)->where('meta_key',$key)->first();
		$metaValue = unserialize($userConfig['meta_value']);
		$metaValue['study_phone_number'] = $value;
		$userConfig->meta_value = serialize($metaValue);
		$userConfig->save();
	}

	public function getRegistrationDateAttribute() {
		return $this->user_registered;
//		$userConfig = $this->userConfig();
//		return $userConfig['registration_date'];
	}

	// birth date
	public function getBirthDateAttribute() {
		$userConfig = $this->userConfig();
		return $userConfig['birth_date'];
	}

	public function setBirthDateAttribute($value) {
		$key = 'wp_'.$this->blogId().'_user_config';
		$userConfig = UserMeta::where('user_id', $this->ID)->where('meta_key',$key)->first();
		$metaValue = unserialize($userConfig['meta_value']);
		$metaValue['birth_date'] = $value;
		$userConfig->meta_value = serialize($metaValue);
		$userConfig->save();
	}

	// gender
	public function getGenderAttribute() {
		$userConfig = $this->userConfig();
		return ($userConfig['gender']) ? $userConfig['gender'] : '';
	}

	public function setGenderAttribute($value) {
		$key = 'wp_'.$this->blogId().'_user_config';
		$userConfig = UserMeta::where('user_id', $this->ID)->where('meta_key',$key)->first();
		$metaValue = unserialize($userConfig['meta_value']);
		$metaValue['gender'] = $value;
		$userConfig->meta_value = serialize($metaValue);
		$userConfig->save();
	}

	public function setEmailAttribute($value) {
		$key = 'wp_'.$this->blogId().'_user_config';
		$userConfig = UserMeta::where('user_id', $this->ID)->where('meta_key',$key)->first();
		$metaValue = unserialize($userConfig['meta_value']);
		$metaValue['email'] = $value;
		$userConfig->meta_value = serialize($metaValue);
		$userConfig->save();
	}

	public function getAgeAttribute() {
		$from = new DateTime($this->birthDate);
		$to   = new DateTime('today');
		return $from->diff($to)->y;
	}

	public function getMonthlyTimeAttribute() {
		$time = $this->meta->where('meta_key', 'cur_month_activity_time')->lists('meta_value');
		if(!empty($time)) {
			return $time[0];
		} else {
			return 0;
		}
	}

	public function getTimeZoneAttribute() {
		$userConfig = $this->userConfig();
		return $userConfig['preferred_contact_timezone'];
	}

	public function getMRNAttribute() {
		$userConfig = $this->userConfig();
		return isset($userConfig['mrn_number']) ? $userConfig['mrn_number'] : '';
	}

	public function setMRNAttribute($value) {
		$key = 'wp_'.$this->blogId().'_user_config';
		$userConfig = UserMeta::where('user_id', $this->ID)->where('meta_key',$key)->first();
		$metaValue = unserialize($userConfig['meta_value']);
		$metaValue['mrn_number'] = $value;
		$userConfig->meta_value = serialize($metaValue);
		$userConfig->save();
	}

	public function getSpecialtyAttribute() {
		$userConfig = $this->userConfig();
		if(isset($userConfig['specialty'])){
			return $userConfig['specialty'];
		} else return '';
	}

	public function getCareTeamAttribute() {
		$userConfig = $this->userConfig();
		if(!isset($userConfig['care_team'])) {
			return array();
		}
		return $userConfig['care_team'];
	}

	public function setCareTeamAttribute($value) {
		if(empty($value)) {
			return false;
		}
		$key = 'wp_'.$this->blogId().'_user_config';
		$userConfig = UserMeta::where('user_id', $this->ID)->where('meta_key',$key)->first();
		$metaValue = unserialize($userConfig['meta_value']);
		$metaValue['care_team'] = $value;
		$userConfig->meta_value = serialize($metaValue);
		$userConfig->save();
		return true;
	}

	public function getSendAlertToAttribute() {
		$userConfig = $this->userConfig();
		if(!isset($userConfig['send_alert_to'])) {
			return array();
		}
		return $userConfig['send_alert_to'];
	}

	public function setSendAlertToAttribute($value) {
		$key = 'wp_'.$this->blogId().'_user_config';
		$userConfig = UserMeta::where('user_id', $this->ID)->where('meta_key',$key)->first();
		$metaValue = unserialize($userConfig['meta_value']);
		$metaValue['send_alert_to'] = $value;
		$userConfig->meta_value = serialize($metaValue);
		$userConfig->save();
		return true;
	}

	public function getBillingProviderIDAttribute() {
		$userConfig = $this->userConfig();
		if(!isset($userConfig['billing_provider'])) {
			return '';
		}
		return $userConfig['billing_provider'];
	}

	public function setBillingProviderIDAttribute($value) {
		$key = 'wp_'.$this->blogId().'_user_config';
		$userConfig = UserMeta::where('user_id', $this->ID)->where('meta_key',$key)->first();
		$metaValue = unserialize($userConfig['meta_value']);
		$metaValue['billing_provider'] = $value;
		$userConfig->meta_value = serialize($metaValue);
		$userConfig->save();
		return true;
	}

	public function getLeadContactIDAttribute() {
		$userConfig = $this->userConfig();
		if(isset($userConfig['lead_contact'])){
			return $userConfig['lead_contact'];
		} else return '';
	}

	public function setLeadContactIDAttribute($value) {
		$key = 'wp_'.$this->blogId().'_user_config';
		$userConfig = UserMeta::where('user_id', $this->ID)->where('meta_key',$key)->first();
		$metaValue = unserialize($userConfig['meta_value']);
		$metaValue['lead_contact'] = $value;
		$userConfig->meta_value = serialize($metaValue);
		$userConfig->save();
		return true;
	}

	public function getPreferredLocationName() {
			$userConfig = $this->userConfig();
		$location = Location::find($userConfig['preferred_contact_location']);
		return (isset($location->name)) ?
			$location->name :
			'';
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
		$userConfig = $this->userConfig();
		return isset($userConfig['agent_name']) ? $userConfig['agent_name'] : '';
	}

	public function setAgentNameAttribute($value) {
		$key = 'wp_'.$this->blogId().'_user_config';
		$userConfig = UserMeta::where('user_id', $this->ID)->where('meta_key',$key)->first();
		$metaValue = unserialize($userConfig['meta_value']);
		$metaValue['agent_name'] = $value;
		$userConfig->meta_value = serialize($metaValue);
		$userConfig->save();
		return true;
	}

	// agent_phone
	public function getAgentPhoneAttribute() {
		$userConfig = $this->userConfig();
		return isset($userConfig['agent_telephone']) ? $userConfig['agent_telephone'] : '';
	}

	public function setAgentPhoneAttribute($value) {
		$key = 'wp_'.$this->blogId().'_user_config';
		$userConfig = UserMeta::where('user_id', $this->ID)->where('meta_key',$key)->first();
		$metaValue = unserialize($userConfig['meta_value']);
		$metaValue['agent_telephone'] = $value;
		$userConfig->meta_value = serialize($metaValue);
		$userConfig->save();
		return true;
	}

	// agent_email
	public function getAgentEmailAttribute() {
		$userConfig = $this->userConfig();
		return isset($userConfig['agent_email']) ? $userConfig['agent_email'] : '';
	}

	public function setAgentEmailAttribute($value) {
		$key = 'wp_'.$this->blogId().'_user_config';
		$userConfig = UserMeta::where('user_id', $this->ID)->where('meta_key',$key)->first();
		$metaValue = unserialize($userConfig['meta_value']);
		$metaValue['agent_email'] = $value;
		$userConfig->meta_value = serialize($metaValue);
		$userConfig->save();
		return true;
	}

	// agent_relationship
	public function getAgentRelationshipAttribute() {
		$userConfig = $this->userConfig();
		return isset($userConfig['agent_relationship']) ? $userConfig['agent_relationship'] : '';
	}

	public function setAgentRelationshipAttribute($value) {
		$key = 'wp_'.$this->blogId().'_user_config';
		$userConfig = UserMeta::where('user_id', $this->ID)->where('meta_key',$key)->first();
		$metaValue = unserialize($userConfig['meta_value']);
		$metaValue['agent_relationship'] = $value;
		$userConfig->meta_value = serialize($metaValue);
		$userConfig->save();
		return true;
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

	public function getCCMStatusAttribute() {
		$meta = $this->meta->where('meta_key', 'ccm_status')->lists('meta_value');
		if(!empty($meta)) {
			return $meta[0];
		}
		return '';
	}

	public function setCCMStatusAttribute($status) {
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

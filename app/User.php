<?php namespace App;

use DateTime;
use Hautelook\Phpass\PasswordHash;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use MikeMcLin\WpPassword\Facades\WpPassword;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Auth;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

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
		"user_id" => "required",
		"daily_reminder_optin" => "required",
		"daily_reminder_time" => "required",
		"daily_reminder_areas" => "required",
		"hospital_reminder_optin" => "required",
		"hospital_reminder_time" => "required",
		"hospital_reminder_areas" => "required",
		"qualification" => "",
		"specialty" => "",
		"npi_number" => "",
		"firstName" => "required",
		"lastName" => "required",
		"gender" => "required",
		"mrn_number" => "required",
		"birth_date" => "required",
		"telephone" => "required",
		"email" => "required",
		"address" => "required",
		"city" => "required",
		"state" => "required",
		"zip" => "required",
		"preferred_contact_time" => "required",
		"timezone" => "required",
		"consent_date" => "required"
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
		if(empty($programIds)) {
			return false;
		}
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
		if(isset($firstName) ) {
			$name .= $firstName->meta_value;
		}
		return $name;
	}

    public function getLastNameAttribute() {
		$name = '';
		$lastName = $this->meta()->where('meta_key', '=', 'last_name')->first();
		if(isset($lastName) ) {
			$name .= $lastName->meta_value;
		}
		return $name;
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

	public function getPhoneAttribute() {
		$userConfig = $this->userConfig();
		return $userConfig['study_phone_number'];
	}

	public function getBirthDateAttribute() {
		$userConfig = $this->userConfig();
		return $userConfig['birth_date'];
	}

	public function getGenderAttribute() {
		$userConfig = $this->userConfig();
		return $userConfig['gender'];
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

	public function getCareTeamAttribute() {
		$userConfig = $this->userConfig();
		return $userConfig['care_team'];
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

	public function getCCMStatus() {
		$status = $this->meta->where('meta_key', 'ccm_status')->lists('meta_value');
		return $status[0];
	}
}

<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Zizaco\Entrust\Traits\EntrustUserTrait;

class WpUser extends Model {

    use EntrustUserTrait; // add this trait to your user model

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

    protected $dates = ['deleted'];

    public $timestamps = false;

    public $rules = array(
        'user_login'             => 'required',                        // just a normal required validation
        'user_email'            => 'required|email',     // required and must be unique in the wp_users table
        'user_pass'         => 'required',
        'user_pass_confirm' => 'required|same:user_pass',           // required and has to match the password field
        'user_nicename'         => 'required',
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
        "qualification" => "required",
        "specialty" => "required",
        "npi_number" => "required",
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

    // Whenever the user_pass field is modified, WordPress' internal hashing function will run
    public function setUserPassAttribute($pass)
    {
        $this->attributes['user_pass'] = wp_hash_password($pass);
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

    public function meta()
    {
        return $this->hasMany('App\WpUserMeta', 'user_id', 'ID');
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

    public function role($blogId = false)
    {
        if(!$blogId) {
            $blogId = $this->blogId();
        }
        $role = WpUserMeta::select('meta_value')->where('user_id', $this->ID)->where('meta_key','wp_'.$blogId.'_capabilities')->first();
        if(!$role) {
            return false;
        } else {
            $data = unserialize($role['meta_value']);
            return key($data);
        }
    }

    public function blogId(){
        $blogID = WpUserMeta::select('meta_value')->where('user_id', $this->ID)->where('meta_key','primary_blog')->first();
        if(!$blogID) {
            return false;
        } else {
            return $blogID['meta_value'];
        }
    }

    public function userConfig(){
        $key = 'wp_'.$this->blogId().'_user_config';
        $userConfig = WpUserMeta::select('meta_value')->where('user_id', $this->ID)->where('meta_key',$key)->first();
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

    public function getWpUserWithMeta($user_id)
    {
        $wpUser = WpUser::where('ID', '=', $user_id)->first();

        return $wpUser;
    }

    public function getWpUsersWithMeta($user_id)
    {
        $wpUsers = WpUser::where('ID', '=', $user_id)->get();

        foreach ( $wpUsers as $wpUser )
        {
            $wpUser['meta'] = $wpUser->meta;
        }

        return $wpUsers;
    }


    public function createNewUser($user_email, $user_pass) {

        // use wordpress md5 hasher class
        $wp_hasher = new \PasswordHash(8, TRUE);
        $user_pass = $wp_hasher->HashPassword($user_pass);

        $this->user_login = $user_email;
        $this->user_email = $user_email;
        $this->user_pass = $user_pass;
        $this->save();

        return true;
    }


    public function userMetaTemplate() {
        $userMeta = array("first_name" => "",
            "last_name" => "",
            "nickname" => "",
            "description" => "",
            "primary_blog" => "",
            "admin_color" => "fresh",
            "cur_month_activity_time" => "0",
        );

        return $userMeta;
    }

    public function userConfigTemplate() {
        $userConfig = array("status" => "Active",
            "email" => "test@test.com",
            "mrn_number" => "12345678",
            "study_phone_number" => "203-252-2556",
            "active_date" => null,
            "preferred_contact_time" => "09:00 AM",
            "preferred_contact_timezone" => "America/New_York",
            "preferred_contact_method" => "SMS",
            "preferred_contact_language" => "EN",
            "preferred_contact_location" => null,
            "prefix" => '',
            "gender" => "M",
            "address" => "123 Test St",
            "city" => "Anywhere",
            "state" => "IA",
            "zip" => "11233",
            "birth_date" => "1900-01-31",
            "consent_date" => "2012-01-31",
            "daily_reminder_optin" => "Y",
            "daily_reminder_time" => "09:00",
            "daily_reminder_areas" => "TBD",
            "hospital_reminder_optin" => "Y",
            "hospital_reminder_time" => "09:00",
            "hospital_reminder_areas" => "TBD",
            "registration_date" => "2015-07-21 01:00:00",
            "care_team" => array(),
            "send_alert_to" => array(),
            "billing_provider" => "",
            "lead_contact" => "",
            "qualification" => "",
            "npi_number" => "",
            "specialty" => "",
        );

        return $userConfig;
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
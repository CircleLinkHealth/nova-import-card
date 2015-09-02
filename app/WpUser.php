<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class WpUser extends Model {

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





    public function meta()
    {
        return $this->hasMany('App\WpUserMeta', 'user_id', 'ID');
    }

    public function comment()
    {
        return $this->hasMany('App\Comment', 'user_id', 'ID');
    }

    public function activities()
    {
        return $this->hasMany('App\Activity');
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

//    public static function getBlogId($user_id){
//
//        $blogID = WpUserMeta::select('meta_value')->where('user_id', $user_id)->where('meta_key','primary_blog')->get();
//        return $blogID;
//    }

    public function createNewUser($user_email, $user_pass) {

        // use wordpress md5 hasher class
        $wp_hasher = new \PasswordHash(8, TRUE);
        $user_pass = $wp_hasher->HashPassword($user_pass);

        $user = new WpUser();
        $user->user_login = $user_email;
        $user->user_email = $user_email;
        $user->user_pass = $user_pass;
        $user_id = $user->save();

        return $user_id;

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
}

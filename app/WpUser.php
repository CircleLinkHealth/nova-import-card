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

    public function role()
    {
        $blogId = $this->blogId();
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

}

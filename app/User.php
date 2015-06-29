<?php namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword;

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
	//protected $table = 'users';
	protected $table = 'wp_users';


	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	//protected $fillable = ['name', 'email', 'password'];
	protected $fillable = array('user_login','user_pass','user_nicename','user_email','user_url');

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('user_pass');

	// WordPress uses differently named fields for create and update fields than Laravel does
	const CREATED_AT = 'post_date';
	const UPDATED_AT = 'post_modified';

	// WordPress uses uppercase "ID" for the primary key
	protected $primaryKey = 'ID';

	public static $rules = array(
		'user_login' => 'required',
		'user_pass' => 'required',
		'user_email' => 'required'
	);

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

    public static function getBlogId($user_id){
        $blogID = WpUserMeta::select('meta_value')->where('user_id', $user_id)->where('meta_key','primary_blog')->first();
        return $blogID['meta_value'];
    }
}

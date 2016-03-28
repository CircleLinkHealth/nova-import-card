<?php namespace App;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Auth;

class UserPatientInfo extends Model {

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
	protected $table = 'user_patient_info';

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
	protected $fillable = ['user_login', 'user_pass', 'user_nicename', 'user_email', 'user_url', 'user_registered', 'user_activation_log', 'user_status', 'auto_attach_programs', 'display_name', 'spam'];

	// for revisionable
	public static function boot()
	{
		parent::boot();
	}

	// START RELATIONSHIPS
	public function user()
	{
		return $this->belongsTo('App\User', 'ID', 'user_id');
	}
	// END RELATIONSHIPS

}

<?php namespace App;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Auth;

class Patient extends Model {

	/**
	 * The connection name for the model.
	 *
	 * @var string
	 */
	protected $connection = 'mysql';

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'patients';

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
	protected $fillable = [
		'user_id',
		'ccda_id',
		'agent_name',
		'agent_telephone',
		'agent_email',
		'agent_relationship',
		'ccm_status',
		'consent_date',
		'cur_month_activity_time',
		'daily_reminder_optin',
		'daily_reminder_time',
		'daily_reminder_areas',
		'hospital_reminder_optin',
		'hospital_reminder_time',
		'hospital_reminder_areas',
		'preferred_cc_contact_days',
		'preferred_contact_time',
		'preferred_contact_timezone',
		'preferred_contact_method',
		'preferred_contact_language',
		'preferred_contact_location',
		'mrn_number',
		'registration_date'];

	// START RELATIONSHIPS
	public function user()
	{
		return $this->belongsTo('App\User', 'ID', 'user_id');
	}
	// END RELATIONSHIPS

}

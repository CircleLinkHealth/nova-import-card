<?php namespace App;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;

class PatientCareTeamMember extends Model {

	const BILLING_PROVIDER = 'billing_provider';
	const LEAD_CONTACT = 'lead_contact';
	const MEMBER = 'member';
	const SEND_ALERT_TO = 'send_alert_to';

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
	protected $table = 'patient_care_team_members';

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
		'member_user_id',
		'type'
	];

	// START RELATIONSHIPS
	public function user()
	{
		return $this->belongsTo('App\User', 'ID', 'user_id');
	}

	public function member()
	{
		return $this->belongsTo('App\User', 'member_user_id', 'ID');
	}
	// END RELATIONSHIPS

}

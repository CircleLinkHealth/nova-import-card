<?php namespace App;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Auth;

class PatientCareTeamProvider extends Model {

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
	protected $table = 'patient_care_team_providers';

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
		'provider_id',
		'type'];

	// START RELATIONSHIPS
	public function user()
	{
		return $this->belongsTo('App\User', 'ID', 'user_id');
	}
	// END RELATIONSHIPS

}

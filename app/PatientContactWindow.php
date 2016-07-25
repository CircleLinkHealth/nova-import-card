<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;

class PatientContactWindow extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'patient_contact_window';

	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primaryKey = 'id';

	protected $guarded = ['id'];

	// START RELATIONSHIPS

	public function patient_info()
	{
		return $this->belongsTo(PatientInfo::class);
	}

	// END RELATIONSHIPS


}

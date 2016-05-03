<?php namespace App\Models\CPM;

use App\CarePlanTemplate;
use App\User;
use Illuminate\Database\Eloquent\Model;

class CpmBiometric extends Model {

	use Instructable;

	protected $table = 'cpm_biometrics';

	protected $guarded = [];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function carePlanTemplates()
	{
		return $this->belongsToMany(CarePlanTemplate::class, 'care_plan_templates_cpm_biometrics');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function patient()
	{
		return $this->belongsToMany(User::class, 'cpm_biometrics_users', 'patient_id');
	}
}

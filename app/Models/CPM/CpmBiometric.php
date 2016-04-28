<?php namespace App\Models\CPM;

use App\CarePlanTemplate;
use Illuminate\Database\Eloquent\Model;

class CpmBiometric extends Model {

	protected $table = 'cpm_biometrics';

	protected $guarded = [];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function carePlanTemplates()
	{
		return $this->belongsToMany(CarePlanTemplate::class, 'care_plan_templates_cpm_biometrics');
	}

}

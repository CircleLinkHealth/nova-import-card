<?php namespace App\Models\CPM;

use Illuminate\Database\Eloquent\Model;

class CpmInstruction extends Model {

	protected $guarded = [];

	public function cpmBiometrics()
	{
		return $this->morphedByMany(CpmBiometrics::class, 'instructables');
	}

	public function cpmLifestyles()
	{
		return $this->morphedByMany(CpmLifestyle::class, 'instructables');
	}

	public function cpmMedicationGroups()
	{
		return $this->morphedByMany(CpmMedicationGroup::class, 'instructables');
	}

	public function cpmMisc()
	{
		return $this->morphedByMany(CpmMisc::class, 'instructables');
	}

	public function cpmProblems()
	{
		return $this->morphedByMany(CpmProblem::class, 'instructables');
	}

	public function cpmSymptom()
	{
		return $this->morphedByMany(CpmSymptom::class, 'instructables');
	}

}

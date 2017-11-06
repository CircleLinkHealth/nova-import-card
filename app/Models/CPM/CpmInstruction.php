<?php namespace App\Models\CPM;

use Illuminate\Database\Eloquent\Model;

class CpmInstruction extends \App\BaseModel
{

    protected $guarded = [];

    public function cpmBiometrics()
    {
        return $this->morphedByMany(CpmBiometric::class, 'instructable');
    }

    public function cpmLifestyles()
    {
        return $this->morphedByMany(CpmLifestyle::class, 'instructable');
    }

    public function cpmMedicationGroups()
    {
        return $this->morphedByMany(CpmMedicationGroup::class, 'instructable');
    }

    public function cpmMisc()
    {
        return $this->morphedByMany(CpmMisc::class, 'instructable');
    }

    public function cpmProblems()
    {
        return $this->morphedByMany(CpmProblem::class, 'instructable');
    }

    public function cpmSymptom()
    {
        return $this->morphedByMany(CpmSymptom::class, 'instructable');
    }
}

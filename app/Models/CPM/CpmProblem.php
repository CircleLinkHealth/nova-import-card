<?php namespace App\Models\CPM;

use App\CarePlanTemplate;
use App\User;
use Illuminate\Database\Eloquent\Model;

class CpmProblem extends Model {

    protected $table = 'cpm_problems';

	protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function carePlanTemplates()
    {
        return $this->belongsToMany(CarePlanTemplate::class, 'care_plan_templates_cpm_problems');
    }
}

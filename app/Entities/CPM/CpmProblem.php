<?php namespace App\Entities\CPM;

use App\CarePlanTemplate;
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

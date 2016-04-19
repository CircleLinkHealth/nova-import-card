<?php namespace App\Entities\CPM;

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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}

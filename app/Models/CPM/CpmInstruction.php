<?php namespace App\Models\CPM;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CPM\CpmInstruction
 *
 * @property int $id
 * @property int $is_default
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstruction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstruction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstruction whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstruction whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstruction whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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

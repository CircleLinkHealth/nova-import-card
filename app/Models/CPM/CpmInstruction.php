<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\CPM;

/**
 * App\Models\CPM\CpmInstruction.
 *
 * @property int            $id
 * @property int            $is_default
 * @property string         $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstruction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstruction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstruction whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstruction whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstruction whereUpdatedAt($value)
 * @mixin \Eloquent
 *
 * @property \App\Models\CPM\CpmBiometric[]|\Illuminate\Database\Eloquent\Collection        $cpmBiometrics
 * @property \App\Models\CPM\CpmLifestyle[]|\Illuminate\Database\Eloquent\Collection        $cpmLifestyles
 * @property \App\Models\CPM\CpmMedicationGroup[]|\Illuminate\Database\Eloquent\Collection  $cpmMedicationGroups
 * @property \App\Models\CPM\CpmMisc[]|\Illuminate\Database\Eloquent\Collection             $cpmMisc
 * @property \App\Models\CPM\CpmProblem[]|\Illuminate\Database\Eloquent\Collection          $cpmProblems
 * @property \App\Models\CPM\CpmSymptom[]|\Illuminate\Database\Eloquent\Collection          $cpmSymptom
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstruction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstruction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstruction query()
 */
class CpmInstruction extends \CircleLinkHealth\Core\Entities\BaseModel
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

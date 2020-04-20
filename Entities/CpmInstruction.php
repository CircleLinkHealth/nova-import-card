<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

/**
 * CircleLinkHealth\SharedModels\Entities\CpmInstruction.
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
 * @property \CircleLinkHealth\SharedModels\Entities\CpmBiometric[]|\Illuminate\Database\Eloquent\Collection       $cpmBiometrics
 * @property \CircleLinkHealth\SharedModels\Entities\CpmLifestyle[]|\Illuminate\Database\Eloquent\Collection       $cpmLifestyles
 * @property \CircleLinkHealth\SharedModels\Entities\CpmMedicationGroup[]|\Illuminate\Database\Eloquent\Collection $cpmMedicationGroups
 * @property \CircleLinkHealth\SharedModels\Entities\CpmMisc[]|\Illuminate\Database\Eloquent\Collection            $cpmMisc
 * @property \CircleLinkHealth\SharedModels\Entities\CpmProblem[]|\Illuminate\Database\Eloquent\Collection         $cpmProblems
 * @property \CircleLinkHealth\SharedModels\Entities\CpmSymptom[]|\Illuminate\Database\Eloquent\Collection         $cpmSymptom
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection           $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstruction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstruction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstruction query()
 *
 * @property int|null $cpm_biometrics_count
 * @property int|null $cpm_lifestyles_count
 * @property int|null $cpm_medication_groups_count
 * @property int|null $cpm_misc_count
 * @property int|null $cpm_problems_count
 * @property int|null $cpm_symptom_count
 * @property int|null $revision_history_count
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

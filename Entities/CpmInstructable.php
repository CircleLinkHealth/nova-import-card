<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

/**
 * CircleLinkHealth\SharedModels\Entities\CpmMiscUser.
 *
 * @property int                                                    $id
 * @property int|null                                               $cpm_instruction_id
 * @property int                                                    $instructable_id
 * @property int                                                    $instruction_type
 * @property \Carbon\Carbon                                         $created_at
 * @property \Carbon\Carbon                                         $updated_at
 * @property \CircleLinkHealth\SharedModels\Entities\CpmInstruction $cpmInstruction
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CpmMisc whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CpmMisc whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CpmMisc whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CpmMisc whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string                                                                                      $instructable_type
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstructable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstructable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstructable query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstructable whereCpmInstructionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstructable whereInstructableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstructable whereInstructableType($value)
 * @property \CircleLinkHealth\SharedModels\Entities\CpmProblem[]|\Illuminate\Database\Eloquent\Collection $cpmProblem
 * @property int|null                                                                                      $cpm_problem_count
 * @property int|null                                                                                      $revision_history_count
 */
class CpmInstructable extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $table = 'instructables';

    public function cpmInstruction()
    {
        return $this->belongsTo(CpmInstruction::class, 'cpm_instruction_id');
    }

    public function cpmProblem()
    {
        return $this->morphedByMany(CpmProblem::class, 'instructable', 'instructables', 'id');
    }

    public function source()
    {
        if ($this->instructable_type) {
            return $this->belongsTo(app($this->instructable_type), 'instructable_id');
        }
    }
}

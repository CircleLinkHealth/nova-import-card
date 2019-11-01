<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\CPM;

/**
 * App\Models\CPM\CpmProblem.
 *
 * @property int                            $id
 * @property int                            $cpm_instruction_id
 * @property int                            $patient_id
 * @property int                            $cpm_problem_id
 * @property \Carbon\Carbon                 $created_at
 * @property \Carbon\Carbon                 $updated_at
 * @property \App\Models\CPM\CpmInstruction $instruction
 * @mixin \Eloquent
 * @property \App\Models\CPM\CpmInstruction[]|\Illuminate\Database\Eloquent\Collection      $cpmInstructions
 * @property \App\Models\CPM\CpmProblem                                                     $problems
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblemUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblemUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblemUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblemUser whereCpmInstructionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblemUser whereCpmProblemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblemUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblemUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblemUser wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblemUser whereUpdatedAt($value)
 * @property \App\Models\CPM\CpmProblem $problem
 * @property int|null                   $cpm_instructions_count
 * @property int|null                   $revision_history_count
 */
class CpmProblemUser extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use Instructable;

    protected $guarded = [];

    protected $table = 'cpm_problems_users';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function instruction()
    {
        return $this->belongsTo(CpmInstruction::class, 'cpm_instruction_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function problem()
    {
        return $this->belongsTo(CpmProblem::class, 'cpm_problem_id');
    }
}

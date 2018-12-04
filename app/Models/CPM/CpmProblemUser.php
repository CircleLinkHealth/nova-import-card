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
 */
class CpmProblemUser extends \App\BaseModel
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
    public function problems()
    {
        return $this->belongsTo(CpmProblem::class, 'cpm_problem_id');
    }
}

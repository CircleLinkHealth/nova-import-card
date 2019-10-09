<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\CPM;

use CircleLinkHealth\Customer\Entities\User;

/**
 * App\Models\CPM\CpmSymptomUser.
 *
 * @property int                                                                                 $id
 * @property int                                                                                 $cpm_symptom_id
 * @property int|null                                                                            $cpm_instruction_id
 * @property int|null                                                                            $patient_id
 * @property \Carbon\Carbon                                                                      $created_at
 * @property \Carbon\Carbon                                                                      $updated_at
 * @property \App\Models\CPM\CpmSymptom                                                          $cpmSymptom
 * @property \App\Models\CPM\CpmInstruction[]|\Illuminate\Database\Eloquent\Collection           $cpmInstructions
 * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptomUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptomUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptomUser whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \App\Models\CPM\CpmInstruction                                                 $instruction
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptomUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptomUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptomUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptomUser whereCpmInstructionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptomUser whereCpmSymptomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptomUser wherePatientId($value)
 * @property int|null $cpm_instructions_count
 * @property int|null $revision_history_count
 */
class CpmSymptomUser extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use Instructable;

    protected $guarded = [];
    protected $table   = 'cpm_symptoms_users';

    public function cpmSymptom()
    {
        return $this->belongsTo(CpmSymptom::class);
    }

    public function instruction()
    {
        return $this->belongsTo(CpmInstruction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}

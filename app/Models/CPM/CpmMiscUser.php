<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\CPM;

use CircleLinkHealth\CarePlanModels\Entities\CpmInstruction;
use CircleLinkHealth\CarePlanModels\Entities\CpmMisc;
use CircleLinkHealth\CarePlanModels\Entities\Instructable;
use CircleLinkHealth\Customer\Entities\User;

/**
 * App\Models\CPM\CpmMiscUser.
 *
 * @property int                                      $id
 * @property int|null                                 $cpm_instruction_id
 * @property int                                      $patient_id
 * @property int                                      $cpm_misc_is
 * @property \Carbon\Carbon                           $created_at
 * @property \Carbon\Carbon                           $updated_at
 * @property \CircleLinkHealth\CarePlanModels\Entities\CpmInstruction           $cpmInstruction
 * @property \CircleLinkHealth\Customer\Entities\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\CarePlanModels\Entities\CpmMisc whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\CarePlanModels\Entities\CpmMisc whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\CarePlanModels\Entities\CpmMisc whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\CarePlanModels\Entities\CpmMisc whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int                                                                            $cpm_misc_id
 * @property \CircleLinkHealth\CarePlanModels\Entities\CpmInstruction[]|\Illuminate\Database\Eloquent\Collection      $cpmInstructions
 * @property \CircleLinkHealth\CarePlanModels\Entities\CpmMisc                                                        $cpmMisc
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMiscUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMiscUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMiscUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMiscUser whereCpmInstructionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMiscUser whereCpmMiscId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMiscUser wherePatientId($value)
 * @property int|null $cpm_instructions_count
 * @property int|null $revision_history_count
 */
class CpmMiscUser extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use Instructable;

    protected $table = 'cpm_miscs_users';

    public function cpmInstruction()
    {
        return $this->belongsTo(CpmInstruction::class, 'cpm_instruction_id')->orderBy('id', 'desc');
    }

    public function cpmMisc()
    {
        return $this->belongsTo(CpmMisc::class, 'cpm_misc_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}

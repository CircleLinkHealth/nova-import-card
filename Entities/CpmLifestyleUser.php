<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use App\Models\CPM\App;
use CircleLinkHealth\Customer\Entities\User;

/**
 * CircleLinkHealth\SharedModels\Entities\CpmLifestyleUser.
 *
 * @property int                                      $id
 * @property int|null                                 $cpm_instruction_id
 * @property int                                      $patient_id
 * @property int                                      $cpm_lifestyle_id
 * @property \Carbon\Carbon                           $created_at
 * @property \Carbon\Carbon                           $updated_at
 * @property App\Models\CPM\CpmInstruction            $cpmInstruction
 * @property App\Models\CPM\CpmLifestyle              $cpmLifestyle
 * @property \CircleLinkHealth\Customer\Entities\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CpmLifestyle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CpmLifestyle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CpmLifestyle whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CpmLifestyle whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\SharedModels\Entities\CpmInstruction[]|\Illuminate\Database\Eloquent\Collection $cpmInstructions
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection       $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyleUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyleUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyleUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyleUser whereCpmInstructionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyleUser whereCpmLifestyleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyleUser wherePatientId($value)
 * @property int|null $cpm_instructions_count
 * @property int|null $revision_history_count
 */
class CpmLifestyleUser extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use Instructable;

    protected $guarded = [];

    protected $table = 'cpm_lifestyles_users';

    public function cpmInstruction()
    {
        return $this->belongsTo(CpmInstruction::class, 'cpm_instruction_id');
    }

    public function cpmLifestyle()
    {
        return $this->belongsTo(CpmLifestyle::class, 'cpm_lifestyle_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}

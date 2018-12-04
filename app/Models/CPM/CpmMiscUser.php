<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\CPM;

use App\User;

/**
 * App\Models\CPM\CpmMiscUser.
 *
 * @property int                            $id
 * @property int|null                       $cpm_instruction_id
 * @property int                            $patient_id
 * @property int                            $cpm_misc_is
 * @property \Carbon\Carbon                 $created_at
 * @property \Carbon\Carbon                 $updated_at
 * @property \App\Models\CPM\CpmInstruction $cpmInstruction
 * @property \App\User                      $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CpmMiscUser extends \App\BaseModel
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

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\CPM;

use CircleLinkHealth\Customer\Entities\User;

/**
 * App\Models\CPM\CpmLifestyleUser.
 *
 * @property int                           $id
 * @property int|null                      $cpm_instruction_id
 * @property int                           $patient_id
 * @property int                           $cpm_lifestyle_id
 * @property \Carbon\Carbon                $created_at
 * @property \Carbon\Carbon                $updated_at
 * @property App\Models\CPM\CpmInstruction $cpmInstruction
 * @property App\Models\CPM\CpmLifestyle   $cpmLifestyle
 * @property \CircleLinkHealth\Customer\Entities\User                     $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CpmLifestyleUser extends \App\BaseModel
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

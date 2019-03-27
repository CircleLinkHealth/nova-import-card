<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * Class PracticeRoleUser
 * @package App
 *
 * @property integer $program_id
 * @property integer $user_id
 * @property integer $role_id
 */
class PracticeRoleUser extends BaseModel
{
    protected $fillable = [
        'program_id',
        'user_id',
        'role_id',
    ];
    protected $table = 'practice_role_user';
}

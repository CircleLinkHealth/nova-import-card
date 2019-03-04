<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

class PracticeRoleUser extends BaseModel
{
    protected $fillable = [
        'program_id',
        'user_id',
        'role_id',
    ];
    protected $table = 'practice_role_user';
}

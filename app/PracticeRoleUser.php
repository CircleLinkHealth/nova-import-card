<?php

namespace App;

use Venturecraft\Revisionable\RevisionableTrait;

class PracticeRoleUser extends BaseModel
{
    use RevisionableTrait;

    protected $table = 'practice_role_user';
}

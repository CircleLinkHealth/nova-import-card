<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use App\User;

/**
 * Class UserRepositoryEloquent.
 */
class UserRepositoryEloquent
{
    public function model()
    {
        return app(User::class);
    }

    public function user($id)
    {
        return $this->model()->findOrFail($id);
    }
}

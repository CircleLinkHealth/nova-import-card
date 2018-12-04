<?php

namespace App\Repositories;

use App\User;

/**
 * Class UserRepositoryEloquent
 * @package namespace App\Repositories;
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

<?php

namespace App\Repositories;

use App\User;
use App\Models\CPM\CpmLifestyleUser;

class CpmLifestyleUserRepository
{
    public function model()
    {
        return app(CpmLifestyleUser::class);
    }

    public function count() {
        return $this->model()->count();
    }
}
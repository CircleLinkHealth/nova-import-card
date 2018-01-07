<?php

namespace App\Repositories;

use App\User;
use App\Patient;
use App\Models\CPM\CpmMiscUser;

class CpmMiscUserRepository
{
    public function model()
    {
        return app(CpmMiscUser::class);
    }

    public function count() {
        return $this->model()->count();
    }
}
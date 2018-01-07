<?php

namespace App\Repositories;

use App\User;
use App\Models\CPM\CpmLifestyle;

class CpmLifestyleRepository
{
    public function model()
    {
        return app(CpmLifestyle::class);
    }

    public function count() {
        return $this->model()->count();
    }
    
    public function lifestyles() {
        return $this->model()->paginate();
    }
}
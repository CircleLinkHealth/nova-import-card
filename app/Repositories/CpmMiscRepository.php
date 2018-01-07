<?php

namespace App\Repositories;

use App\User;
use App\Patient;
use App\Models\CPM\CpmMisc;

class CpmMiscRepository
{
    public function model()
    {
        return app(CpmMisc::class);
    }

    public function count() {
        return $this->model()->count();
    }
    
    public function misc() {
        return $this->model()->paginate();
    }
}
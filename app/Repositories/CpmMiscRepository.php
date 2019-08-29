<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use App\Models\CPM\CpmMisc;

class CpmMiscRepository
{
    public function count()
    {
        return $this->model()->count();
    }

    public function exists($id)
    {
        return (bool) $this->model()->find($id);
    }

    public function model()
    {
        return app(CpmMisc::class);
    }
}

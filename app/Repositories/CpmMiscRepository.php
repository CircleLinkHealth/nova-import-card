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

    public function misc($id = null)
    {
        if ($id) {
            $misc = $this->model()->with('carePlanTemplates')->find($id);
            if ($misc) {
                return $this->setupMisc($misc);
            }

            return null;
        }

        return $this->model()->get()->map([$this, 'setupMisc']);
    }

    public function model()
    {
        return app(CpmMisc::class);
    }

    public function setupMisc($misc)
    {
        $misc['patients'] = $misc->users()->count();

        return $misc;
    }
}

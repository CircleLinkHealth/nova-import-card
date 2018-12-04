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

    public function count()
    {
        return $this->model()->count();
    }
    
    public function setupMisc($misc)
    {
        $misc['patients'] = $misc->users()->count();
        return $misc;
    }
    
    public function misc($id = null)
    {
        if ($id) {
            $misc = $this->model()->with('carePlanTemplates')->find($id);
            if ($misc) {
                return $this->setupMisc($misc);
            } else {
                return null;
            }
        } else {
            return $this->model()->get()->map([$this, 'setupMisc']);
        }
    }

    public function exists($id)
    {
        return !!$this->model()->find($id);
    }
}

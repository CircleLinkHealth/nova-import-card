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

    public function count()
    {
        return $this->model()->count();
    }

    public function setupLifestyle($lifestyle)
    {
        $lifestyle['patients'] = $lifestyle->users()->count();
        return $lifestyle;
    }
    
    public function lifestyles()
    {
        $lifestyles = $this->model()->paginate();
        $lifestyles->getCollection()->transform([$this, 'setupLifestyle']);
        return $lifestyles;
    }
    
    public function lifestyle($id)
    {
        return $this->setupLifestyle($this->model()->with(['carePlanTemplates'])->find($id));
    }

    public function exists($id)
    {
        return !!$this->model()->find($id);
    }
}

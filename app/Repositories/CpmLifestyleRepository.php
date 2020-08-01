<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use CircleLinkHealth\SharedModels\Entities\CpmLifestyle;

class CpmLifestyleRepository
{
    public function count()
    {
        return $this->model()->count();
    }

    public function exists($id)
    {
        return (bool) $this->model()->find($id);
    }

    public function lifestyle($id)
    {
        return $this->setupLifestyle($this->model()->with(['carePlanTemplates'])->find($id));
    }

    public function lifestyles()
    {
        $lifestyles = $this->model()->paginate();
        $lifestyles->getCollection()->transform([$this, 'setupLifestyle']);

        return $lifestyles;
    }

    public function model()
    {
        return app(CpmLifestyle::class);
    }

    public function setupLifestyle($lifestyle)
    {
        $lifestyle['patients'] = $lifestyle->users()->count();

        return $lifestyle;
    }
}

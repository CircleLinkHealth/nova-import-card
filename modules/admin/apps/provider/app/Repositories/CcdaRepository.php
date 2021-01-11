<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use CircleLinkHealth\SharedModels\Entities\Ccda;

class CcdaRepository
{
    public function ccda($id = null)
    {
        if ($id) {
            return $this->setupCcda($this->model()->findOrFail($id));
        }
        $ccda = $this->model()->exclude(['xml', 'json'])->paginate();
        $ccda->getCollection()->transform([$this, 'setupCcda']);

        return $ccda;
    }

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
        return app(Ccda::class);
    }

    public function setupCcda($ccda)
    {
        $ccda->xml  = null;
        $ccda->json = null;

        return $ccda;
    }
}

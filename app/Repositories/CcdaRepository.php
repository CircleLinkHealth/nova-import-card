<?php

namespace App\Repositories;

use App\Models\MedicalRecords\Ccda;

class CcdaRepository
{
    public function model()
    {
        return app(Ccda::class);
    }

    public function count()
    {
        return $this->model()->count();
    }
    
    public function setupCcda($ccda)
    {
        $ccda->xml = null;
        $ccda->json = null;
        return $ccda;
    }
    
    public function ccda($id = null)
    {
        if ($id) {
            return $this->setupCcda($this->model()->findOrFail($id));
        } else {
            $ccda = $this->model()->exclude([ 'xml', 'json' ])->paginate();
            $ccda->getCollection()->transform([$this, 'setupCcda']);
            return $ccda;
        }
    }

    public function exists($id)
    {
        return !!$this->model()->find($id);
    }
}

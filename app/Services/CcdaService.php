<?php

namespace App\Services;

use App\Repositories\CcdaRepository;

class CcdaService
{
    private $ccdaRepo;

    public function __construct(CcdaRepository $ccdaRepo) {
        $this->ccdaRepo = $ccdaRepo;
    }

    public function repo() {
        return $this->ccdaRepo;
    }

    public function ccda($id = null) {
        return $this->repo()->ccda($id);
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use CircleLinkHealth\CarePlanModels\Entities\Ccda;
use App\Repositories\CcdaRepository;

class CcdaService
{
    private $ccdaRepo;

    public function __construct(CcdaRepository $ccdaRepo)
    {
        $this->ccdaRepo = $ccdaRepo;
    }

    public function ccda($id = null)
    {
        return $this->repo()->ccda($id);
    }

    public function create(Ccda $ccda, $xml)
    {
        $ccda->vendor_id = 1;
        $ccda->source    = Ccda::IMPORTER;
        $ccda->save();
        $ccda = $ccda->storeCcd($xml);
        $ccda->import();

        return $ccda;
    }

    public function repo()
    {
        return $this->ccdaRepo;
    }
}

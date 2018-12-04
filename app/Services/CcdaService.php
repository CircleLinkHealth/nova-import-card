<?php

namespace App\Services;

use App\Models\MedicalRecords\Ccda;
use App\Repositories\CcdaRepository;

class CcdaService
{
    private $ccdaRepo;

    public function __construct(CcdaRepository $ccdaRepo)
    {
        $this->ccdaRepo = $ccdaRepo;
    }

    public function repo()
    {
        return $this->ccdaRepo;
    }

    public function ccda($id = null)
    {
        return $this->repo()->ccda($id);
    }

    public function create(Ccda $ccda, $xml)
    {
        $ccda->vendor_id = 1;
        $ccda->source = Ccda::IMPORTER;
        $ccda->save();
        $ccda = $ccda->storeCcd($xml);
        $ccda->import();

        return $ccda;
    }
}

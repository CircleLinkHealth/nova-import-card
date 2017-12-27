<?php namespace App\Services;

use App\CarePlan;
use App\Repositories\CareplanRepository;

class CareplanService
{
    private $careplanRepo;

    public function __construct(CareplanRepository $careplanRepo) {
        $this->careplanRepo = $careplanRepo;
    }

    public function repo() {
        return $this->careplanRepo;
    }
}

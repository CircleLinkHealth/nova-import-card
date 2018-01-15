<?php namespace App\Services;

use App\Repositories\ProblemCodeRepository;
use App\Repositories\ProblemCodeSystemRepository;

class ProblemCodeService
{
    private $problemCodeRepo;

    public function __construct(ProblemCodeRepository $problemCodeRepo, ProblemCodeSystemRepository $problemCodeSystemRepo) {
        $this->problemCodeRepo = $problemCodeRepo;
        $this->problemCodeSystemRepo = $problemCodeSystemRepo;
    }

    public function repo() {
        return $this->problemCodeRepo;
    }

    public function systems() {
        return $this->problemCodeSystemRepo->model()->get();
    }
    
    public function system($id) {
        return $this->problemCodeSystemRepo->model()->findOrFail($id);
    }
}
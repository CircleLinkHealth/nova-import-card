<?php namespace App\Services;

use App\Repositories\CareplanAssessmentRepository;

class CareplanAssessmentService
{
    private $assessmentRepo;

    public function __construct(CareplanAssessmentRepository $assessmentRepo) {
        $this->assessmentRepo = $assessmentRepo;
    }

    public function repo() {
        return $this->assessmentRepo;
    }
}

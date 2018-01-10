<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 01/10/2018
 * Time: 8:56 PM
 */

namespace App\Services;


use App\Repositories\PatientReadRepository;

class PrintPausedPatientLettersService
{
    private $patientReadRepository;

    public function __construct(PatientReadRepository $patientReadRepository)
    {
        $this->patientReadRepository = $patientReadRepository;
    }

    public function getPausedPatients() {
        return $this->patientReadRepository
            ->paused()
            ->get();
    }
}
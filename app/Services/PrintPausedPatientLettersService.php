<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 01/10/2018
 * Time: 8:56 PM
 */

namespace App\Services;


use App\Repositories\PatientReadRepository;
use Carbon\Carbon;

class PrintPausedPatientLettersService
{
    private $patientReadRepository;

    public function __construct(PatientReadRepository $patientReadRepository)
    {
        $this->patientReadRepository = $patientReadRepository;
    }

    public function getPausedPatients()
    {
        return $this->patientReadRepository
            ->paused()
            ->pausedLetterNotPrinted()
            ->fetch()
            ->map(function ($patient) {
                return [
                    'patient_name' => $patient->fullName,
                    'first_name'   => $patient->first_name,
                    'last_name'    => $patient->last_name,
                    'link'         => route('patient.careplan.print', ['patientId' => $patient->id]),
                    'reg_date'     => Carbon::parse($patient->registrationDate)->format('m/d/Y'),
                    'paused_date'  => $patient->date_paused->format('m/d/Y'),
                    'provider'     => $patient->billingProviderName,
                    'program_name' => $patient->primaryPracticeName,
                ];
            });
    }
}
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

    /**
     * Get paused patients whose letter has not been printed yet
     *
     * @return \Illuminate\Support\Collection|static
     */
    public function getPausedPatients()
    {
        return $this->patientReadRepository
            ->paused()
            ->pausedLetterNotPrinted()
            ->fetch()
            ->map(function ($patient) {
                return [
                    'id'           => $patient->id,
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

    public function makePausedLettersPdf(array $userIdsToPrint)
    {

    }
}
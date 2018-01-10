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

    public function getPausedPatients() {
        return $this->patientReadRepository
            ->paused()
            ->get()
            ->map(function($patient) {
                    return [
                        'patient_name'               => $patient->fullName,
                        'first_name'                 => $patient->first_name,
                        'last_name'                  => $patient->last_name,
                        'careplan_status'            => $patient->carePlanStatus,
                        'careplan_provider_approver' => $patient->carePlan->provider_approver_name,
                        'dob'                        => Carbon::parse($patient->birthDate)->format('m/d/Y'),
                        'phone'                      => '',
                        'age'                        => $patient->age,
                        'reg_date'                   => Carbon::parse($patient->registrationDate)->format('m/d/Y'),
                        'last_read'                  => '',
                        'ccm_time'                   => $patient->patientInfo->cur_month_activity_time,
                        'ccm_seconds'                => $patient->patientInfo->cur_month_activity_time,
                        'provider'                   => $patient->billingProviderName,
                        'program_name'               => $patient->primaryPracticeName,
                        'careplan_last_printed'      => '',
                        'careplan_printed'           => '',
                    ];
            });
    }
}
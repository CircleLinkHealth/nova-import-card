<?php

namespace App\Services\AthenaAPI;

use App\TargetPatient;
use App\ValueObjects\Athena\ProblemsAndInsurances;
use Carbon\Carbon;

class DetermineEnrollmentEligibility
{
    protected $api;

    protected $athenaEhrId = 2;

    public function __construct(Calls $api)
    {
        $this->api = $api;
    }

    public function getPatientIdFromAppointments(
        $ehrPracticeId,
        Carbon $startDate,
        Carbon $endDate
    ) {
        $start = $startDate->format('m/d/Y');
        $end   = $endDate->format('m/d/Y');

        $departments = $this->api->getDepartmentIds($ehrPracticeId);

        foreach ($departments['departments'] as $department) {
            $response = $this->api->getBookedAppointments($ehrPracticeId, $start, $end, $department['departmentid']);

            if ( ! isset($response['appointments'])) {
                return;
            }

            if (count($response['appointments']) == 0) {
                return;
            }

            foreach ($response['appointments'] as $bookedAppointment) {
                $ehrPatientId = $bookedAppointment['patientid'];
                $departmentId = $bookedAppointment['departmentid'];

                if ( ! $ehrPatientId) {
                    continue;
                }

                $target = TargetPatient::updateOrCreate([
                    'ehr_id'            => $this->athenaEhrId,
                    'ehr_patient_id'    => $ehrPatientId,
                    'ehr_practice_id'   => $ehrPracticeId,
                    'ehr_department_id' => $departmentId,
                ]);

                if ( ! $target->status) {
                    $target->status = 'to_process';
                    $target->save();
                }
            }

        }
    }

    /**
     * @param $patientId
     * @param $practiceId
     * @param $departmentId
     *
     * @return ProblemsAndInsurances
     */
    public function getPatientProblemsAndInsurances($patientId, $practiceId, $departmentId)
    {
        $problemsResponse   = $this->api->getPatientProblems($patientId, $practiceId, $departmentId);
        $insurancesResponse = $this->api->getPatientInsurances($patientId, $practiceId, $departmentId);

        $problemsAndInsurance = new ProblemsAndInsurances();
        $problemsAndInsurance->setProblems($problemsResponse['problems']);
        $problemsAndInsurance->setInsurances($insurancesResponse['insurances']);

        return $problemsAndInsurance;
    }
}
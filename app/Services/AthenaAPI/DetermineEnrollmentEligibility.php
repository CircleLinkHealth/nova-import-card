<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\AthenaAPI;

use App\TargetPatient;
use Carbon\Carbon;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\Eligibility\Jobs\Athena\GetAppointmentsForDepartment;

class DetermineEnrollmentEligibility
{
    private $api;

    public function __construct(AthenaApiImplementation $api)
    {
        $this->api = $api;
    }

    public function determineEnrollmentEligibility(TargetPatient $targetPatient)
    {
        $targetPatient->loadMissing(['batch', 'practice']);

        $ccda = $this->createCcdaFromAthena($targetPatient);

        $job   = new CheckCcdaEnrollmentEligibility($ccda, $targetPatient->practice, $targetPatient->batch);
        $check = $job->handle();

        $targetPatient->eligibility_job_id = $check->getEligibilityJob()->id;

        $targetPatient->setStatusFromEligibilityJob($check->getEligibilityJob());

        $targetPatient->save();
    }

    public function getDemographics($patientId, $practiceId)
    {
        return $this->api->getDemographics($patientId, $practiceId);
    }

    /**
     * @param $ehrPracticeId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param bool   $offset
     * @param null   $batchId
     *
     * @throws \Exception
     */
    public function getPatientIdFromAppointments(
        $ehrPracticeId,
        Carbon $startDate,
        Carbon $endDate,
        $offset = false,
        $batchId = null
    ) {
        $departments = \Cache::tags(['athena_api', "ehr_practice_id:$ehrPracticeId"])->remember("athena_api:$ehrPracticeId:department_ids", 5, function () use ($ehrPracticeId) {
            return $this->api->getDepartmentIds($ehrPracticeId);
        });

        $count = count($departments);

        \Log::channel('logdna')->info("Found $count departments", [
            'batch_id'        => $batchId,
            'ehr_practice_id' => $ehrPracticeId,
        ]);

        foreach ($departments['departments'] as $department) {
            if ( ! empty($department['departmentid'])) {
                GetAppointmentsForDepartment::dispatch($department['departmentid'], $ehrPracticeId, $startDate, $endDate, $offset, $batchId);
            }
        }
    }

    public function getPatientInsurances($patientId, $practiceId, $departmentId)
    {
        $insurancesResponse = $this->api->getPatientInsurances($patientId, $practiceId, $departmentId);

        $insurances = new Insurances();
        $insurances->setInsurances($insurancesResponse['insurances']);

        return $insurances;
    }

    /**
     * @param $patientId
     * @param $practiceId
     * @param $departmentId
     *
     * @return Problems
     */
    public function getPatientProblems($patientId, $practiceId, $departmentId)
    {
        $problemsResponse = $this->api->getPatientProblems($patientId, $practiceId, $departmentId);

        $problems = new Problems();
        $problems->setProblems($problemsResponse['problems']);

        return $problems;
    }
}

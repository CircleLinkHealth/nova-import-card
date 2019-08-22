<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\AthenaAPI;

use App\Jobs\CheckCcdaEnrollmentEligibility;
use App\Models\MedicalRecords\Ccda;
use App\TargetPatient;
use App\ValueObjects\Athena\Insurances;
use App\ValueObjects\Athena\Problems;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;

class DetermineEnrollmentEligibility
{
    protected $api;

    protected $athenaEhrId = 2;

    public function __construct(Calls $api)
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

    public function getPatientIdFromAppointments(
        $ehrPracticeId,
        Carbon $startDate,
        Carbon $endDate,
        $offset = false,
        $batchId = null
    ) {
        $start = $startDate->format('m/d/Y');
        $end   = $endDate->format('m/d/Y');

        $departments = $this->api->getDepartmentIds($ehrPracticeId);

        foreach ($departments['departments'] as $department) {
            $offsetBy = 0;

            if ($offset) {
                $offsetBy = TargetPatient::where('ehr_practice_id', $ehrPracticeId)
                    ->where('ehr_department_id', $department['departmentid'])
                    ->count();
            }

            $response = $this->api->getBookedAppointments(
                $ehrPracticeId,
                $start,
                $end,
                $department['departmentid'],
                $offsetBy
            );

            if ( ! isset($response['appointments'])) {
                return;
            }

            if (0 == count($response['appointments'])) {
                return;
            }

            foreach ($response['appointments'] as $bookedAppointment) {
                $ehrPatientId = $bookedAppointment['patientid'];
                $departmentId = $bookedAppointment['departmentid'];

                if ( ! $ehrPatientId) {
                    continue;
                }

                $target = TargetPatient::updateOrCreate([
                    'practice_id'       => Practice::where('external_id', $ehrPracticeId)->value('id'),
                    'ehr_id'            => $this->athenaEhrId,
                    'ehr_patient_id'    => $ehrPatientId,
                    'ehr_practice_id'   => $ehrPracticeId,
                    'ehr_department_id' => $departmentId,
                ]);

                if (null !== $batchId) {
                    $target->batch_id = $batchId;
                }

                if ( ! $target->status) {
                    $target->status = 'to_process';
                    $target->save();
                }
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

    /**
     * @param TargetPatient $targetPatient
     *
     * @throws \Exception
     *
     * @return \App\Importer\MedicalRecordEloquent|Ccda|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    private function createCcdaFromAthena(TargetPatient $targetPatient)
    {
        $athenaApi = app(Calls::class);

        $ccdaExternal = $athenaApi->getCcd(
            $targetPatient->ehr_patient_id,
            $targetPatient->ehr_practice_id,
            $targetPatient->ehr_department_id
        );

        if ( ! isset($ccdaExternal[0])) {
            throw new \Exception('Could not retrieve CCD from Athena for '.TargetPatient::class.':'.$targetPatient->id);
        }

        return Ccda::create([
            'practice_id' => $targetPatient->practice_id,
            'vendor_id'   => 1,
            'xml'         => $ccdaExternal[0]['ccda'],
            'status'      => Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY,
            'source'      => Ccda::ATHENA_API,
            'imported'    => false,
            'batch_id'    => $targetPatient->batch_id,
        ]);
    }

    private function practiceFromExternalId($ehrPracticeId): Practice
    {
        return Practice::where(
            'external_id',
            '=',
            $ehrPracticeId
        )->firstOrFail();
    }
}

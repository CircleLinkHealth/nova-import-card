<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\AthenaAPI;

use App\Adapters\EligibilityCheck\AthenaAPIAdapter;
use App\EligibilityBatch;
use App\EligibilityJob;
use App\Enrollee;
use App\TargetPatient;
use App\ValueObjects\Athena\ProblemsAndInsurances;
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
        $patientInfo = $this->getPatientProblemsAndInsurances(
            $targetPatient->ehr_patient_id,
            $targetPatient->ehr_practice_id,
            $targetPatient->ehr_department_id
        );

        $batch   = EligibilityBatch::find($this->argument('batchId'));
        $adapter = new AthenaAPIAdapter(
            $patientInfo,
            new EligibilityJob(['batch_id' => $batch->id]),
            $batch
        );
        $isEligible = $adapter->isEligible();

        $job                               = $adapter->getEligibilityJob();
        $targetPatient->eligibility_job_id = $job->id;

        if ( ! $isEligible) {
            $targetPatient->status = 'ineligible';
            $targetPatient->save();

            return false;
        }

        $demos = $this->getDemographics(
            $targetPatient->ehr_patient_id,
            $targetPatient->ehr_practice_id
        );

        try {
            $demos = $demos[0];
        } catch (\Exception $e) {
            throw new \Exception('Get Demographics api call failed.', 500, $e);
        }

        if ($demos['homephone'] or $demos['mobilephone']) {
            $targetPatient->status = 'eligible';
        } else {
            $targetPatient->status      = 'error';
            $targetPatient->description = 'Homephone or mobile phone must be provided';
        }

        $practice = Practice::where(
            'external_id',
            '=',
            $targetPatient->ehr_practice_id
        )->first();

        if ( ! $practice) {
            throw new \Exception(
                "Practice with AthenaId {$targetPatient->ehr_practice_id} was not found.",
                500
            );
        }

        $insurances = $patientInfo->getInsurances();

        try {
            $enrollee = Enrollee::create([
                //required
                'first_name'  => $demos['firstname'],
                'last_name'   => $demos['lastname'],
                'home_phone'  => $demos['homephone'],
                'cell_phone'  => $demos['mobilephone'] ?? null,
                'practice_id' => $practice->id,
                'batch_id'    => $this->argument('batchId') ?? null,

                'status' => Enrollee::TO_CALL,

                //notRequired
                'address'   => $demos['address1'] ?? null,
                'address_2' => $demos['address2'] ?? null,
                'dob'       => $demos['dob'],
                'state'     => $demos['state'],
                'city'      => $demos['city'] ?? null,
                'zip'       => $demos['zip'] ?? null,

                'primary_insurance' => array_key_exists(0, $insurances)
                    ? $insurances[0]['insurancetype'] ?? $insurances[0]['insuranceplanname']
                    : '',
                'secondary_insurance' => array_key_exists(1, $insurances)
                    ? $insurances[1]['insurancetype'] ?? $insurances[1]['insuranceplanname']
                    : '',
                'tertiary_insurance' => array_key_exists(2, $insurances)
                    ? $insurances[2]['insurancetype'] ?? $insurances[2]['insuranceplanname']
                    : '',

                'cpm_problem_1' => $adapter->getEligiblePatientList()->first()->get('cpm_problem_1'),
                'cpm_problem_2' => $adapter->getEligiblePatientList()->first()->get('cpm_problem_2'),
            ]);

            $targetPatient->enrollee_id = $enrollee->id;
        } catch (\Exception $e) {
            //check if this is a mysql exception for unique key constraint
            if ($e instanceof \Illuminate\Database\QueryException) {
                $errorCode = $e->errorInfo[1];
                if (1062 == $errorCode) {
                    //do nothing
                    //we don't actually want to terminate the program if we detect duplicates
                    //we just don't wanna add the row again
                }
            }
        }

        $targetPatient->save();

        return $enrollee ?? null;
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

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\AthenaAPI;

use App\TargetPatient;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;

class DetermineEnrollmentEligibility
{
    protected $api;

    protected $athenaEhrId = 2;

    public function __construct(AthenaApiImplementation $api)
    {
        $this->api = $api;
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

                $target = TargetPatient::updateOrCreate(
                    [
                        'practice_id'       => Practice::where('external_id', $ehrPracticeId)->value('id'),
                        'ehr_id'            => $this->athenaEhrId,
                        'ehr_patient_id'    => $ehrPatientId,
                        'ehr_practice_id'   => $ehrPracticeId,
                        'ehr_department_id' => $departmentId,
                    ]
                );

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
}

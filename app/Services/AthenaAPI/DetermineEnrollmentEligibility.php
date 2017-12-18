<?php

namespace App\Services\AthenaAPI;

use App\CLH\CCD\Importer\QAImportManager;
use App\Contracts\Repositories\CcdaRepository;
use App\Contracts\Repositories\CcdaRequestRepository;
use App\TargetPatient;
use Carbon\Carbon;
use Maknz\Slack\Facades\Slack;

class DetermineEnrollmentEligibility
{
    protected $api;
    protected $ccdaRequests;
    protected $ccdas;

    public function __construct(CcdaRequestRepository $ccdaRequests, CcdaRepository $ccdas, Calls $api)
    {
        $this->api          = $api;
        $this->ccdaRequests = $ccdaRequests;
        $this->ccdas        = $ccdas;
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
                    'ehr_patient_id',
                    $ehrPatientId,
                    'ehr_practice_id',
                    $ehrPracticeId,
                    'ehr_department_id',
                    $departmentId,
                ]);

                if ( ! $target->status) {
                    $target->status = 'to_process';
                    $target->save();
                }
            }

        }
    }

    public function getPatientProblemsAndInsurances($patientId, $practiceId, $departmentId)
    {

        $problemsResponse   = $this->service->api->getPatientProblems($patientId, $practiceId, $departmentId);
        $insurancesResponse = $this->service->api->getPatientInsurances($patientId, $practiceId, $departmentId);

        $patientInfo = [$problemsResponse['problems'], $insurancesResponse['insurances']];

        return $patientInfo;


    }
}

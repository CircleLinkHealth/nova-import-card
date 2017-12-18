<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 26/08/16
 * Time: 1:16 PM
 */

namespace App\Services\AthenaAPI;

use App\CLH\CCD\Importer\QAImportManager;
use App\Contracts\Repositories\CcdaRepository;
use App\Contracts\Repositories\CcdaRequestRepository;
use App\Models\CCD\CcdVendor;
use App\Models\MedicalRecords\Ccda;
use App\TargetPatient;
use Carbon\Carbon;
use Maknz\Slack\Facades\Slack;

class Service
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

                if (!$ehrPatientId) {
                    continue;
                }

                $target = TargetPatient::updateOrCreate([
                    'ehr_patient_id', $ehrPatientId,
                    'ehr_practice_id', $ehrPracticeId,
                    'ehr_department_id', $departmentId,
                ]);

                if (!$target->status) {
                    $target->status = 'to_process';
                    $target->save();
                }
            }

        }
    }

    public function getAppointments(
        $practiceId,
        Carbon $startDate,
        Carbon $endDate
    ) {
        $start = $startDate->format('m/d/Y');
        $end   = $endDate->format('m/d/Y');

        $departments = $this->api->getDepartmentIds($practiceId);

        foreach ($departments['departments'] as $department) {
            $response = $this->api->getBookedAppointments($practiceId, $start, $end, $department['departmentid']);
            $this->logPatientIdsFromAppointments($response, $practiceId);
        }
    }

    public function logPatientIdsFromAppointments($response, $practiceId)
    {
        if ( ! isset($response['appointments'])) {
            return;
        }

        if (count($response['appointments']) == 0) {
            return;
        }

        $practiceCustomFields = $this->api->getPracticeCustomFields($practiceId);

        //Get 'CCM Enabled' custom field id from the practice's custom fields
        foreach ($practiceCustomFields as $customField) {
            if (strtolower($customField['name']) == 'ccm enabled') {
                $ccmEnabledFieldId = $customField['customfieldid'];
            }
        }

        if ( ! isset($ccmEnabledFieldId)) {
            return;
        }

        foreach ($response['appointments'] as $bookedAppointment) {
            $patientId    = $bookedAppointment['patientid'];
            $departmentId = $bookedAppointment['departmentid'];

            $patientCustomFields = $this->api->getPatientCustomFields($patientId, $practiceId, $departmentId);

            //If 'CCM Enabled' contains a y (meaning yes), then save the patient id
            foreach ($patientCustomFields as $customField) {
                if ($customField['customfieldid'] == $ccmEnabledFieldId
                    && str_contains($customField['customfieldvalue'], ['Y', 'y'])
                ) {
                    $ccdaRequest = $this->ccdaRequests->create([
                        'patient_id'    => $patientId,
                        'department_id' => $departmentId,
                        'vendor'        => 'athena',
                        'practice_id'   => $practiceId,
                    ]);
                }
            }
        }

        if (isset($response['next'])) {
            $this->logPatientIdsFromAppointments($this->api->getNextPage($response['next']), $practiceId);
        }
    }

    public function getCcdsFromRequestQueue($number = 5)
    {
        $ccdaRequests = $this->ccdaRequests
            ->skipPresenter()
            ->findWhere([
                'successful_call' => null,
            ])->take($number);

        $imported = $ccdaRequests->map(function ($ccdaRequest) {
            $xmlCcda = $this->api->getCcd(
                $ccdaRequest->patient_id,
                $ccdaRequest->practice_id,
                $ccdaRequest->department_id
            );

            if ( ! isset($xmlCcda[0]['ccda'])) {
                return false;
            }

            $vendor = CcdVendor::wherePracticeId($ccdaRequest->practice_id)->first();

            if ( ! $vendor) {
                return false;
            }

            $ccda = $this->ccdas->create([
                'xml'       => $xmlCcda[0]['ccda'],
                'vendor_id' => $vendor->id,
                'source'    => Ccda::ATHENA_API,
            ]);

            $ccdaRequest->ccda_id         = $ccda->id;
            $ccdaRequest->successful_call = true;
            $ccdaRequest->save();

            $ccda->import();

            if (app()->environment('worker')) {
                $link = route('view.files.ready.to.import');

                sendSlackMessage(
                    '#ccd-file-status',
                    "We received a CCD from Athena. \n Please visit {$link} to import."
                );
            }

            return $ccda;
        });
    }

    public function postPatientDocument($patientId, $practiceId, $attachmentContentPath, $departmentId)
    {
        return $this->api->postPatientDocument($patientId, $practiceId, $attachmentContentPath, $departmentId);
    }



    public function getPatientProblemsAndInsurances($patientId, $practiceId, $departmentId){

        $problemsResponse = $this->service->api->getPatientProblems($patientId, $practiceId, $departmentId);
        $insurancesResponse = $this->service->api->getPatientInsurances($patientId, $practiceId, $departmentId);

        $patientInfo = [$problemsResponse['problems'], $insurancesResponse['insurances']];

        return $patientInfo;



    }
}

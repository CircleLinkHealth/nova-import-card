<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\AthenaAPI;

use App\Entities\CcdaRequest;
use App\Jobs\ImportCcda;
use Carbon\Carbon;
use CircleLinkHealth\Eligibility\Services\AthenaAPI\Calls;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Support\Str;

class CreateAndPostPdfCareplan
{
    protected $api;
    protected $ccdas;

    public function __construct(Calls $api)
    {
        $this->api = $api;
    }

    public function getAppointments(
        $practiceId,
        Carbon $startDate,
        Carbon $endDate
    ) {
        $start = $startDate->format('m/d/Y');
        $end   = $endDate->format('m/d/Y');

        $departments = $this->api->getDepartments($practiceId);

        if ( ! is_array($departments) || ! array_key_exists('departments', $departments)) {
            return false;
        }

        foreach ($departments['departments'] as $department) {
            $response = $this->api->getBookedAppointments($practiceId, $start, $end, $department['departmentid']);
            $this->logPatientIdsFromAppointments($response, $practiceId);
        }
    }

    public function getCcdsFromRequestQueue($number = 5)
    {
        $imported = CcdaRequest::whereNull('successful_call')
            ->chunkById($number, function ($ccdaRequests) {
                foreach ($ccdaRequests as $ccdaRequest) {
                    $xmlCcda = $this->api->getCcd(
                        $ccdaRequest->patient_id,
                        $ccdaRequest->practice_id,
                        $ccdaRequest->department_id
                    );

                    if ( ! isset($xmlCcda[0]['ccda'])) {
                        return false;
                    }

                    $ccda = Ccda::create([
                        'xml'    => $xmlCcda[0]['ccda'],
                        'source' => Ccda::ATHENA_API,
                    ]);

                    $ccdaRequest->ccda_id = $ccda->id;
                    $ccdaRequest->successful_call = true;
                    $ccdaRequest->save();

                    ImportCcda::dispatch($ccda)->onQueue('low');

                    if (isProductionEnv()) {
                        $link = route('import.ccd.remix');

                        sendSlackMessage(
                            '#ccd-file-status',
                            "We received a CCD from Athena. \n Please visit {$link} to import."
                        );
                    }

                    return $ccda;
                }
            });
    }

    public function logPatientIdsFromAppointments($response, $practiceId)
    {
        if ( ! isset($response['appointments'])) {
            return;
        }

        if (0 == count($response['appointments'])) {
            return;
        }

        $practiceCustomFields = $this->api->getPracticeCustomFields($practiceId);

        //Get 'CCM Enabled' custom field id from the practice's custom fields
        foreach ($practiceCustomFields as $customField) {
            if ('ccm enabled' == strtolower($customField['name'])) {
                $ccmEnabledFieldId = $customField['customfieldid'];
            }
        }

        if ( ! isset($ccmEnabledFieldId)) {
            return;
        }

        foreach ($response['appointments'] as $bookedAppointment) {
            $patientId    = $bookedAppointment['patientid'];
            $departmentId = $bookedAppointment['departmentid'];

            $patientCustomFields = $this->api->getPatientCustomFields($patientId, $practiceId, $departmentId) ?? [];

            //If 'CCM Enabled' contains a y (meaning yes), then save the patient id
            foreach ($patientCustomFields as $customField) {
                if ($customField['customfieldid'] == $ccmEnabledFieldId
                    && Str::contains($customField['customfieldvalue'], ['Y', 'y'])
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

    public function postPatientDocument($patientId, $practiceId, $attachmentContentPath, $departmentId)
    {
        return $this->api->postPatientDocument($patientId, $practiceId, $attachmentContentPath, $departmentId);
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 26/08/16
 * Time: 1:16 PM
 */

namespace App\Services\AthenaAPI;


use App\Contracts\Repositories\CcdaRequestRepository;
use Carbon\Carbon;

class Service
{
    protected $api;
    protected $ccdaRequests;

    public function __construct(CcdaRequestRepository $ccdaRequests)
    {
        $this->api = new Calls();
        $this->ccdaRequests = $ccdaRequests;
    }

    public function getTodayCcds($practiceId)
    {
        $today = Carbon::today()->format('m/d/Y');

        $response = $this->api->getBookedAppointments($practiceId, $today, $today, false, 1000, 1);
        $this->logPatientIdsFromAppointments($response);
    }

    public function logPatientIdsFromAppointments($response)
    {
        foreach ($response['appointments'] as $bookedAppointment) {
            $this->ccdaRequests->create([
                'patient_id' => $bookedAppointment['patientid'],
                'department_id' => $bookedAppointment['departmentid'],
                'vendor' => 'athena'
            ]);
        }

        if (isset($response['next'])) $this->logPatientIdsFromAppointments($this->api->getNextPage($response['next']));
    }


    public function getCcd($patientId = 3212, $departmentId = 1, $practiceId = 1959188)
    {
        return $this->api->getCcd($patientId, $practiceId, $departmentId);
    }

    public function getPatientCustomFields($patientId = 909, $departmentId = 1, $practiceId = 1959188)
    {
        return $this->api->getPatientCustomFields($patientId, $practiceId, $departmentId);
    }

    public function postPatientDocument(
        $patientId = 909,
        $practiceId = 1959188,
        $attachmentContent = null,
        $documentSubClass = 'CLINICALDOCUMENT',
        $contentType = 'multipart/form-data'
    )
    {
        $attachmentContent = realpath(storage_path('/pdfs/careplans/sample-careplan.pdf'));

        return $this->api->postPatientDocument($patientId, $practiceId, "@$attachmentContent", $documentSubClass = 'CLINICALDOCUMENT', $contentType = 'multipart/form-data');
    }
}
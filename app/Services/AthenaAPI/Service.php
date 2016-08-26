<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 26/08/16
 * Time: 1:16 PM
 */

namespace App\Services\AthenaAPI;


use Carbon\Carbon;

class Service
{
    protected $api;

    public function __construct()
    {
        $this->api = new Calls();
    }

    public function getTodayCcds($practiceId)
    {
        $today = Carbon::today()->format('m/d/y');

        $bookedAppointments = $this->api->getBookedAppointments($practiceId, $today, $today, false, 1000, 1);

        dd($bookedAppointments);

        foreach ($bookedAppointments as $appointment)
        {

        }


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
    ){
        $attachmentContent = realpath(storage_path('/pdfs/careplans/sample-careplan.pdf'));

        return $this->api->postPatientDocument($patientId, $practiceId, "@$attachmentContent", $documentSubClass = 'CLINICALDOCUMENT', $contentType = 'multipart/form-data');
    }
}
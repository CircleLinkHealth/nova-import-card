<?php

namespace App\Http\Controllers\CcdApi\Athena;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Services\AthenaAPI\APICalls;
use Carbon\Carbon;

class AthenaApiController extends Controller
{
    protected $api;

    public function __construct()
    {
        $this->api = new APICalls();
    }

    public function getCcd($patientId = 3212, $departmentId = 1, $practiceId = 1959188)
    {
        return $this->api->getCcd($patientId, $practiceId, $departmentId);
    }

    public function getBookedAppointments()
    {
        return $this->api->getBookedAppointments($practiceId = 1959188, '05/01/2016', '08/01/2016', false, 3);
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



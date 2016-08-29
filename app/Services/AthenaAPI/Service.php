<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 26/08/16
 * Time: 1:16 PM
 */

namespace App\Services\AthenaAPI;


use App\Contracts\Repositories\CcdaRepository;
use App\Contracts\Repositories\CcdaRequestRepository;
use Carbon\Carbon;

class Service
{
    protected $api;
    protected $ccdaRequests;
    protected $ccdas;

    public function __construct(CcdaRequestRepository $ccdaRequests, CcdaRepository $ccdas)
    {
        $this->api = new Calls();
        $this->ccdaRequests = $ccdaRequests;
        $this->ccdas = $ccdas;
    }

    public function getAppointmentsForToday($practiceId)
    {
        $today = Carbon::today()->format('m/d/Y');

        $response = $this->api->getBookedAppointments($practiceId, $today, $today, false, 1000, 1);
        $this->logPatientIdsFromAppointments($response, $practiceId);
    }

    public function logPatientIdsFromAppointments($response, $practiceId)
    {
        foreach ($response['appointments'] as $bookedAppointment) {
            $this->ccdaRequests->create([
                'patient_id' => $bookedAppointment['patientid'],
                'department_id' => $bookedAppointment['departmentid'],
                'vendor' => 'athena',
                'practice_id' => $practiceId,
            ]);
        }

        if (isset($response['next'])) $this->logPatientIdsFromAppointments($this->api->getNextPage($response['next']), $practiceId);
    }

    public function getCcdsFromRequestQueue($number = 5)
    {
        $ccdaRequests = $this->ccdaRequests->skipPresenter()->all()->take($number);

        foreach ($ccdaRequests as $ccdaRequest) {
            $xmlCcda = $this->api->getCcd($ccdaRequest->patient_id, $ccdaRequest->practice_id, $ccdaRequest->department_id);

            if (!$xmlCcda) continue;

            $ccda = $this->ccdas->create([
                'xml' => $xmlCcda,
            ]);

            $ccdaRequest->ccda_id = $ccda->id;
            $ccdaRequest->save();
        }
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
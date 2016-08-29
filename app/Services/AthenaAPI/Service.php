<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 26/08/16
 * Time: 1:16 PM
 */

namespace App\Services\AthenaAPI;


use App\CLH\CCD\Importer\QAImportManager;
use App\CLH\CCD\ItemLogger\CcdItemLogger;
use App\CLH\Repositories\CCDImporterRepository;
use App\Contracts\Repositories\CcdaRepository;
use App\Contracts\Repositories\CcdaRequestRepository;
use App\Models\CCD\Ccda;
use App\Models\CCD\CcdVendor;
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

        $response = $this->api->getBookedAppointments($practiceId, '05/01/2016', $today, false, 1000, 1);
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
        $ccdaRequests = $this->ccdaRequests
            ->skipPresenter()
            ->findWhere([
                'successful_call' => null,
            ])->take($number);

        $imported = $ccdaRequests->map(function ($ccdaRequest) {
            $xmlCcda = $this->api->getCcd($ccdaRequest->patient_id, $ccdaRequest->practice_id, $ccdaRequest->department_id);

            if (!isset($xmlCcda[0]['ccda'])) return false;

            $vendor = CcdVendor::wherePracticeId($ccdaRequest->practice_id)->first();

            if (!$vendor) return false;

            $ccda = $this->ccdas->create([
                'xml' => $xmlCcda[0]['ccda'],
                'vendor_id' => $vendor->id,
                'source' => Ccda::ATHENA_API,
            ]);

            $ccdaRequest->ccda_id = $ccda->id;
            $ccdaRequest->successful_call = true;
            $ccdaRequest->save();

            $repo = new CCDImporterRepository();

            $json = $repo->toJson($ccda->xml);
            $ccda->json = $json;
            $ccda->save();

            $logger = new CcdItemLogger($ccda);
            $logger->logAll();

            $importer = new QAImportManager($vendor->program_id, $ccda);
            $output = $importer->generateCarePlanFromCCD();

            $ccda->imported = true;
            $ccda->save();

            return $ccda;
        });
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
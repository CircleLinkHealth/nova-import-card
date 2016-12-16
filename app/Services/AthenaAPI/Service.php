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
use Maknz\Slack\Facades\Slack;

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

    public function getAppointments(
        $practiceId,
        Carbon $startDate,
        Carbon $endDate
    )
    {
        $start = $startDate->format('m/d/Y');
        $end = $endDate->format('m/d/Y');

        $departments = $this->api->getDepartmentIds($practiceId);

        foreach ($departments['departments'] as $department)
        {
            $response = $this->api->getBookedAppointments($practiceId, $start, $end, $department['departmentid']);
            $this->logPatientIdsFromAppointments($response, $practiceId);
        }
    }

    public function logPatientIdsFromAppointments($response, $practiceId)
    {
        if (!isset($response['appointments'])) {
            return;
        }

        $practiceCustomFields = $this->api->getPracticeCustomFields($practiceId);

        //Get 'CCM Enabled' custom field id from the practice's custom fields
        foreach ($practiceCustomFields as $customField) {
            if (strtolower($customField['name']) == 'ccm enabled') {
                $ccmEnabledFieldId = $customField['customfieldid'];
            }
        }

        if (!isset($ccmEnabledFieldId)) {
            return;
        }

        foreach ($response['appointments'] as $bookedAppointment) {

            $patientId = $bookedAppointment['patientid'];
            $departmentId = $bookedAppointment['departmentid'];

            $patientCustomFields = $this->api->getPatientCustomFields($patientId, $practiceId, $departmentId);

            //If 'CCM Enabled' contains a y (meaning yes), then save the patient id
            foreach ($patientCustomFields as $customField) {
                if ($customField['customfieldid'] == $ccmEnabledFieldId
                    && str_contains($customField['customfieldvalue'], ['Y', 'y'])
                ) {
                    $ccdaRequest = $this->ccdaRequests->create([
                        'patient_id' => $patientId,
                        'department_id' => $departmentId,
                        'vendor' => 'athena',
                        'practice_id' => $practiceId,
                    ]);
                }
            }
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

            if (app()->environment('worker')) {
                $link = 'https://www.careplanmanager.com/ccd-importer/qaimport';

                Slack::to('#ccd-file-status')
                    ->send("We received a CCD from Athena. \n Please visit {$link} to import.");
            }

            return $ccda;
        });
    }

    public function postPatientDocument($patientId, $practiceId, $attachmentContentPath, $departmentId)
    {
        return $this->api->postPatientDocument($patientId, $practiceId, $attachmentContentPath, $departmentId);
    }
}
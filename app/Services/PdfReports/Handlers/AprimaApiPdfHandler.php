<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 21/12/2016
 * Time: 5:58 PM
 */

namespace App\Services\PdfReports\Handlers;


use App\Contracts\PdfReport;
use App\Contracts\PdfReportHandler;
use App\ForeignId;
use App\PatientCareTeamMember;
use App\PatientReports;
use App\User;

class AprimaApiPdfHandler implements PdfReportHandler
{

    /**
     * Dispatch a PDFReport to an API, or EMR Direct Mailbox.
     *
     * @param PdfReport $report
     *
     * @return mixed
     */
    public function pdfHandle(PdfReport $report)
    {
        //assuming relation patient exists and it returns a user object
        $patient = $report->patient;

        $careTeam = $patient->patientCareTeamMembers->where('type', PatientCareTeamMember::SEND_ALERT_TO);

        //ProviderId of the Users this was sent to
        $sendTo = [];

        foreach ($careTeam as $member) {
            $providerId = $member->member_user_id;

            if (in_array($providerId, $sendTo)) {
                continue;
            }

            $provider = User::find($providerId);

            if (!$provider) {
                return false;
            }

            $locationId = $patient->getpreferredContactLocationAttribute();

            if (empty($locationId)) {
                return false;
            }

            //get foreign provider id
            $foreign_id = ForeignId::where('user_id', $providerId)->where('system', ForeignId::APRIMA)->first();

            if (empty($foreign_id)) {
                \Log::error("Provider $providerId has no Aprima Foreign id.");

                continue;
            }

            //update the foreign id to include a location as well
            if (empty($foreign_id->location_id)) {
                $foreign_id->location_id = $locationId;
                $foreign_id->save();
            }

            $file_name = $report->toPdf();

            $base_64_report = base64_encode(file_get_contents($file_name));

            $patientReport = PatientReports::create([
                'patient_id'  => $patient->id,
                'patient_mrn' => $patient->getMRNAttribute(),
                'provider_id' => $foreign_id->foreign_id,
                'file_type'   => get_class($report),
                'file_base64' => $base_64_report,
                'location_id' => $locationId,
            ]);

            $sendTo[] = $providerId;
        }
    }
}
<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\PdfReports\Handlers;

use CircleLinkHealth\Core\Contracts\PdfReport;
use App\Contracts\PdfReportHandler;
use CircleLinkHealth\Eligibility\Services\AthenaAPI\CreateAndPostPdfCareplan as AthenaApi;

class AthenaApiPdfHandler implements PdfReportHandler
{
    public function __construct(AthenaApi $athenaApi)
    {
        $this->athenaApi = $athenaApi;
    }

    /**
     * Dispatch a PDFReport to an API, or EMR Direct Mailbox.
     *
     * @return mixed
     */
    public function pdfHandle(PdfReport $report)
    {
        $pathToPdf = $report->toPdf();

        try {
            $ccda = $report->patient
                ->latestCcda();

            if ( ! $ccda) {
                return false;
            }

            $ccdaRequest = $ccda->ccdaRequest;

            if ( ! $ccdaRequest) {
                return false;
            }

            $response = $this->athenaApi->postPatientDocument(
                $ccdaRequest->patient_id,
                $ccdaRequest->practice_id,
                $pathToPdf,
                $ccdaRequest->department_id
            );

            $decodedResponse = json_decode($response, true);

            if ( ! is_array($decodedResponse)) {
                $line = __METHOD__.__LINE__;
                throw new \Exception("Athena Response is not an array on: ${line}");
            }
        } catch (\Exception $e) {
            \Log::error($e);

            return false;
        }

        if (key_exists('documentid', $decodedResponse)) {
            \Log::info("Sent {$report->id} {$decodedResponse['documentid']}");

            return true;
        }

        return false;
    }
}

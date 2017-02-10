<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 10/02/2017
 * Time: 3:01 PM
 */

namespace App\Services\PdfReports\Handlers;


use App\Contracts\PdfReport;
use App\Contracts\PdfReportHandler;
use App\Location;
use App\Services\PhiMail\PhiMail;
use Carbon\Carbon;

class EmrDirectPdfHandler implements PdfReportHandler
{
    /**
     * @var PhiMail
     */
    private $phiMail;

    public function __construct(PhiMail $phiMail)
    {
        $this->phiMail = $phiMail;
    }

    /**
     * Dispatch a PDFReport to an API, or EMR Direct Mailbox.
     *
     * @param PdfReport $report
     *
     * @return mixed
     */
    public function pdfHandle(PdfReport $report)
    {
        $pathToPdf = $report->toPdf();

        $location = Location::find($report->patient->preferredContactLocation);

        if (!$location) {
            return;
        }

        if (!$location->contactCard) {
            return;
        }

        if ($recipient = $location->contactCard->emr_direct != null) {
            return;
        }

        $fileName = $report->patient->fullName . ' ' . Carbon::now()->toDateTimeString();

        $this->phiMail->send($recipient, $pathToPdf, $fileName);
    }
}
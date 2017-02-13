<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 06/02/2017
 * Time: 8:34 PM
 */

namespace App\Services\PdfReports\Handlers;


use App\Contracts\Efax;
use App\Contracts\PdfReport;
use App\Contracts\PdfReportHandler;
use App\Location;

class EFaxPdfHandler implements PdfReportHandler
{
    protected $efax;

    public function __construct(Efax $efax)
    {
        $this->efax = $efax;
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
        $location = Location::find($report->patient->preferredContactLocation);

        if (!$location) {
            return;
        }

        if (!$location->fax) {
            return;
        }

        $pathToPdf = $report->toPdf();

        $result = $this->efax->send($location->fax, $pathToPdf);

        return $result;
    }
}
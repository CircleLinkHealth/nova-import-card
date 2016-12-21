<?php namespace App\Services\PdfReports\Dispatchers;

use App\Contracts\PdfReport;
use App\Contracts\PdfReportHandler;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 19/12/2016
 * Time: 2:24 PM
 */
class QueueForPickupPdfHandler implements PdfReportHandler
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

    }
}
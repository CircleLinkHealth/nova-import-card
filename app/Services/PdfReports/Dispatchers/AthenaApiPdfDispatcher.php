<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 19/12/2016
 * Time: 4:27 PM
 */

namespace App\Services\PdfReports\Dispatchers;


use App\Contracts\PdfReport;
use App\Contracts\PdfReportHandler;

class AthenaApiPdfHandler implements PdfReportHandler
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
        // TODO: Implement pdfHandle() method.
    }
}
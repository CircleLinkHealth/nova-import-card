<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 19/12/2016
 * Time: 1:32 PM
 */

namespace App\Contracts;


interface PdfReportDispatcher
{
    /**
     * Dispatch a PDFReport to an API, or EMR Direct Mailbox.
     *
     * @param PdfReport $report
     *
     * @return mixed
     */
    public function pdfDispatch(PdfReport $report);
}
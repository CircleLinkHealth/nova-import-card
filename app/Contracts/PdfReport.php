<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 19/12/2016
 * Time: 1:31 PM
 */

namespace App\Contracts;

interface PdfReport extends Pdfable
{
    /**
     * Dispatch the PDF report.
     *
     * @return mixed
     */
    public function pdfHandleCreated();

    /**
     * Get the PDF dispatcher.
     *
     * @return PdfReportHandler
     */
    public function pdfReportHandler() : PdfReportHandler;
}

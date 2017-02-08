<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 28/12/2016
 * Time: 9:49 PM
 */

namespace App\Traits;

use App\Contracts\PdfReportHandler;
use App\Services\PdfReports\Handlers\EFaxPdfHandler;


trait PdfReportTrait
{
    /**
     * Dispatch the PDF report.
     *
     * @return mixed
     */
    public function pdfHandleCreated()
    {
        $this->eFaxHandler()
            ->pdfHandle($this);

        if (!$this->hasPdfHandler()) {
            return false;
        }

        $this->pdfReportHandler()
            ->pdfHandle($this);
    }

    public function eFaxHandler()
    {
        return app(EFaxPdfHandler::class);
    }

    /**
     * Check whether this PDFable has a pdf handler
     *
     * @return bool
     */
    public function hasPdfHandler() : bool
    {
        $practice = $this->patient
            ->primaryPractice;

        if (!$practice->ehr) {
            return false;
        }

        if (!$practice->ehr->pdf_report_handler) {
            return false;
        }

        return true;
    }

    /**
     * Get the PDF dispatcher.
     *
     * @return PdfReportHandler
     */
    public function pdfReportHandler() : PdfReportHandler
    {
        return app($this->patient
            ->primaryPractice
            ->ehr
            ->pdf_report_handler);
    }
}
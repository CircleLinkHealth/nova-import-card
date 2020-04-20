<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

use App\Contracts\PdfReportHandler;

trait PdfReportTrait
{
    /**
     * Check whether this PDFable has a pdf handler.
     */
    public function hasPdfHandler(): bool
    {
        $practice = $this->patient
            ->primaryPractice;

        if ( ! $practice->ehr) {
            return false;
        }

        if ( ! $practice->ehr->pdf_report_handler) {
            return false;
        }

        return true;
    }

    /**
     * Dispatch the PDF report.
     *
     * @return mixed
     */
    public function pdfHandleCreated()
    {
        // Check if we have a designated report handler for this EHR, for example an API.
        if ( ! $this->hasPdfHandler()) {
            return false;
        }

        //And if we do, then handle the PDF accordingly
        $this->pdfReportHandler()
            ->pdfHandle($this);
    }

    /**
     * Get the PDF dispatcher.
     */
    public function pdfReportHandler(): PdfReportHandler
    {
        return app($this->patient
            ->primaryPractice
            ->ehr
            ->pdf_report_handler);
    }
}

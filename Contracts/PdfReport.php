<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Contracts;

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
     */
    public function pdfReportHandler(): PdfReportHandler;
}

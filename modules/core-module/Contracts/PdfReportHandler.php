<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Contracts;

interface PdfReportHandler
{
    /**
     * Dispatch a PDFReport to an API, or EMR Direct Mailbox.
     *
     * @return mixed
     */
    public function pdfHandle(PdfReport $report);
}

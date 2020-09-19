<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Services;

use Barryvdh\Snappy\PdfWrapper;
use CircleLinkHealth\Core\Services\HtmlToPdfService;

class SnappyPdfWrapper extends PdfWrapper implements HtmlToPdfService
{
    /**
     * Return a handler for the pdf service.
     *
     * @return mixed
     */
    public function handler()
    {
        return app('snappy.pdf.wrapper');
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\PdfService\Services;

use Barryvdh\Snappy\PdfWrapper;

class SnappyPdfWrapper extends PdfWrapper
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

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use CircleLinkHealth\Core\HtmlToPdfService;
use Barryvdh\Snappy\PdfWrapper;

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

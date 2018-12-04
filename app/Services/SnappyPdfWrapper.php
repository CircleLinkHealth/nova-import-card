<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 01/11/2018
 * Time: 12:23 AM
 */

namespace App\Services;

use App\Contracts\HtmlToPdfService;
use Barryvdh\Snappy\PdfWrapper;

class SnappyPdfWrapper extends PdfWrapper implements HtmlToPdfService
{

    /**
     * Return a handler for the pdf service
     *
     * @return mixed
     */
    public function handler()
    {
        return app('snappy.pdf.wrapper');
    }
}

<?php

namespace App\Contracts;

/**
 * This is a wrapper for the service used to generate PDFs from html.
 *
 * Interface HtmlToPdfService
 * @package App\Contracts
 */
interface HtmlToPdfService
{
    /**
     * Return a handler for the pdf service
     *
     * @return mixed
     */
    public function handler();
}

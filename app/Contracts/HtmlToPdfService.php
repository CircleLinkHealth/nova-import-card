<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

/**
 * This is a wrapper for the service used to generate PDFs from html.
 *
 * Interface HtmlToPdfService
 */
interface HtmlToPdfService
{
    /**
     * Return a handler for the pdf service.
     *
     * @return mixed
     */
    public function handler();
}

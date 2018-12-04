<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

interface Pdfable
{
    /**
     * Create a PDF of this resource and return the path to it.
     *
     * @param mixed|null $scale
     *
     * @return string
     */
    public function toPdf($scale = null): string;
}

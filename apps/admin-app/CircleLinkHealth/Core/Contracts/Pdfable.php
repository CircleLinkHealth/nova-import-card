<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Contracts;

interface Pdfable
{
    /**
     * Create a PDF of this resource and return the path to it.
     *
     * @param mixed|null $scale
     */
    public function toPdf($scale = null): ?string;
}

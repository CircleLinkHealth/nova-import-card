<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\PdfService\Services;

/**
 * This is a wrapper for the service used to generate PDFs from html.
 *
 * Interface HtmlToPdfService
 */
interface HtmlToPdfService
{
    public function loadHTML(string $htmlString): HtmlToPdfService;

    public function loadView(string $viewName, array $args): HtmlToPdfService;

    public function save(string $filename, bool $overwrite = false): HtmlToPdfService;

    public function setOption(string $name, $value): HtmlToPdfService;
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\PdfService\Services;

use Barryvdh\Snappy\PdfWrapper;

class SnappyPdfWrapper implements HtmlToPdfService
{
    private PdfWrapper $handler;

    public function __construct()
    {
        $this->handler = app('snappy.pdf.wrapper');
        $this->handler->setTemporaryFolder(storage_path('tmp'));
    }

    public function loadHTML(string $htmlString): HtmlToPdfService
    {
        $this->handler->loadHTML($htmlString);

        return $this;
    }

    public function loadView(string $viewName, array $args): HtmlToPdfService
    {
        $this->handler->loadView($viewName, $args);

        return $this;
    }

    public function save(string $filename, bool $overwrite = false): HtmlToPdfService
    {
        $this->handler->save($filename, $overwrite);

        return $this;
    }

    public function setOption(string $name, $value): HtmlToPdfService
    {
        $this->handler->setOption($name, $value);

        return $this;
    }
}

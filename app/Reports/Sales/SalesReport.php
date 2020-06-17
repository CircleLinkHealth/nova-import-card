<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Reports\Sales;

use Carbon\Carbon;
use CircleLinkHealth\Core\Services\PdfService;

abstract class SalesReport
{
    protected $data;
    protected $end;
    protected $for;
    protected $requestedSections;
    protected $start;

    public function __construct(
        $for,
        $sections,
        Carbon $start,
        Carbon $end
    ) {
        $this->for               = $for;
        $this->start             = $start;
        $this->end               = $end;
        $this->requestedSections = $sections;
    }

    public function data()
    {
        foreach ($this->requestedSections as $key => $section) {
            $this->data[$section] = (new $section(
                $this->for,
                $this->start,
                $this->end
            ))->render();
        }

        return $this->data;
    }

    public function renderPDF(
        $name,
        $view
    ) {
        $pdfservice = app(PdfService::class);

        $this->data();
        $filePath = storage_path("download/${name}.pdf");

        $pdf = $pdfservice->createPdfFromView($view, ['data' => $this->data], $filePath);

        return $name.'.pdf';
    }

    public function renderView($name)
    {
        $this->data();

        return view($name, ['data' => $this->data]);
    }
}

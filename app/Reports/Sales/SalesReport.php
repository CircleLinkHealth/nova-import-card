<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Reports\Sales;

use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Carbon\Carbon;

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
        $this->data();
        $pdf = PDF::loadView($view, ['data' => $this->data]);
        $pdf->save(storage_path("download/${name}.pdf"), true);

        return $name.'.pdf';
    }

    public function renderView($name)
    {
        $this->data();

        return view($name, ['data' => $this->data]);
    }
}

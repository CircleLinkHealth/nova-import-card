<?php
/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 12/19/16
 * Time: 5:16 PM
 */

namespace App\Reports\Sales;

use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Carbon\Carbon;

abstract class SalesReport
{
    protected $start;
    protected $end;
    protected $for;
    protected $requestedSections;
    protected $data;

    public function __construct(
        $for,
        $sections,
        Carbon $start,
        Carbon $end
    ) {
        $this->for = $for;
        $this->start = $start;
        $this->end = $end;
        $this->requestedSections = $sections;
    }

    public function renderView($name)
    {
        $this->data();

        return view($name, ['data' => $this->data]);
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
        $pdf->save(storage_path("download/$name.pdf"), true);

        return $name . '.pdf';
    }
}

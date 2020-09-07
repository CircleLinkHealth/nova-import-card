<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Reports\Sales;

use Carbon\Carbon;
use CircleLinkHealth\Core\Services\PdfService;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;

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

        $this->data['practice_id'] = $this->getPracticeId();

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

    private function getPracticeId()
    {
        if ($this->for instanceof User) {
            return $this->for->program_id;
        }

        if ($this->for instanceof Practice) {
            return $this->for->id;
        }

        if ($this->for instanceof Location) {
            return $this->for->practice_id;
        }

        return null;
    }
}

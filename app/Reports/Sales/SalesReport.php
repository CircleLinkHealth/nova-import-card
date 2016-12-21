<?php
/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 12/19/16
 * Time: 5:16 PM
 */

namespace App\Reports\Sales;

use Carbon\Carbon;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

abstract class SalesReport
{

    protected $start;
    protected $end;
    protected $for;
    protected $requestedSections;
    protected $data;

    public function __construct($for, $sections, Carbon $start, Carbon $end)
    {

        $this->for = $for;
        $this->startRange = $start;
        $this->endRange = $end;
        $this->requestedSections = $sections;

    }

    public function data(){

        foreach ($this->requestedSections as $key => $section){

            $this->data[$key] = (new $section(
                $this->for, $this->start, $this->end
            ))->renderSection();

        }

        return $this->data;

    }
    public function renderView(){

    }

    public function renderPDF($name, $view, $data)
    {

        $pdf = PDF::loadView($view, ['data' => $data]);

        $pdf->save( storage_path("download/$name.pdf"), true );

        return $name.'.pdf';
    }

}
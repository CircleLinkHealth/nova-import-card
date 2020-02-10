<?php
/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\OCR;


use CircleLinkHealth\Customer\Entities\Media;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader\PdfReader;
use Spatie\PdfToText\Pdf;


class UPGPdfCarePlan
{
    protected $filePath;

    protected $string;

    protected $carePlan;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function read()
    {
        return $this
            ->getText()
            ->parseString()
            ->categorize();
    }

    private function getText()
    {
        //crop PDF
        //set .env var and config var

        $pdf = new Fpdi();

        $pageCount = $pdf->setSourceFile($this->filePath);
        $pdf->AddPage();

        for ($n = 1; $n <= $pageCount; $n ++){
            $tplId = $pdf->importPage($n);

            $pdf->useTemplate($tplId, 0, -60, 210, 400);
            $pdf->AddPage();
        }


        $pdf->Output(storage_path('testarw2.pdf'), 'F');


        Pdf::getText(storage_path('testarw2.pdf'), '/usr/local/bin/pdftotext', ['layout', 'nopgbrk']);

        return $this;
    }

    private function parseString()
    {
        $this->carePlan = $this->string;

//        $this->carePlan = explode('\n', $this->string);
//        $this->carePlan = preg_split("/[\n]/", $this->string);

        return $this;
    }

    private function categorize()
    {




//        $pageCount = $pdf->setSourceFile($this->filePath);
//
//        $width = $pdf->GetPageWidth();
//        $height = 0;
//
//        $_x = $x = 10;
//        $_y = $y = 10;
//
//        $pdf->AddPage();
//        for ($n = 1; $n <= $pageCount; $n++) {
//            $pageId = $pdf->importPage($n);
//
//            $size = $pdf->useImportedPage($pageId, $x, $y, $width);
//            $pdf->Rect($x, $y, $size['width'], $size['height']);
//            $height = max($height, $size['height']);
//            if ($n % 2 == 0) {
//                $y += $height + 10;
//                $x = $_x;
//                $height = 0;
//            } else {
//                $x += $width + 10;
//            }
//
//
//                $pdf->AddPage();
//                $x = $_x;
//                $y = $_y;
//
//        }



        return $this->string;

    }

}
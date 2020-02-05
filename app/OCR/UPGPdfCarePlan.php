<?php
/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\OCR;


use Spatie\Image\Image;
use Spatie\Image\Manipulations;
use Spatie\PdfToImage\Pdf;
use thiagoalessio\TesseractOCR\TesseractOCR;

class UPGPdfCarePlan
{
    protected $fileName;

    protected $noOfPages;

    public function __construct($fileName)
    {
        $this->fileName = $fileName;
    }

    public function read()
    {
        return $this
            ->convert()
            ->crop()
            ->analyze();
    }

    private function crop()
    {
        for ($i = $this->noOfPages; $i > 0; $i --){
            $image = Image::load(storage_path("{$this->fileName}{$i}.png"));

            $image->crop(Manipulations::CROP_BOTTOM, 595, 760)->save();
            $image->crop(Manipulations::CROP_TOP, 595, 670)->save();
        }

        return $this;
    }

    private function convert()
    {
        //todo: set specific directory

        ini_set('memory_limit', -1);
        try{
            $pdf = new Pdf(storage_path($this->fileName));

            foreach (range(1, $this->noOfPages = $pdf->getNumberOfPages()) as $pageNumber) {
                $pdf->setPage($pageNumber)
                    ->setOutputFormat('png')
                    ->setResolution(300)
                    ->saveImage(storage_path("{$this->fileName}{$pageNumber}.png"));
            }
        }catch (\Exception $exception){
            //
        }

        return $this;
    }

    private function analyze(){

        for ($i = $this->noOfPages; $i > 0; $i --){
            $t = new TesseractOCR(storage_path("{$this->fileName}{$i}.png"));
            //handle exceptions
            $array[] = $t->run();
        }

        return implode('\n', $array);

    }

}
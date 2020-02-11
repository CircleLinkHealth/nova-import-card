<?php
/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\OCR;


use setasign\Fpdi\Fpdi;
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
        $pdf = new Fpdi();

        $pageCount = $pdf->setSourceFile($this->filePath);
        $pdf->AddPage();

        for ($n = 1; $n <= $pageCount; $n++) {
            $tplId = $pdf->importPage($n);

            $pdf->useTemplate($tplId, 0, -60, 210, 400);
            $pdf->AddPage();
        }

        $pdf->Output(storage_path('testarw2.pdf'), 'F');

        //set .env var and config var
        $this->string = Pdf::getText(storage_path('testarw2.pdf'), '/usr/local/bin/pdftotext', ['layout', 'nopgbrk']);

        return $this;
    }

    private function parseString()
    {
//        $this->carePlan = explode('\n', $this->string);
        $array = collect(preg_split("/[\n]/", $this->string))->filter()->values()->all();

        $carePlan = [];

        //to add details dynamically for each checkpoint make these arrays
        $checkpoints = [
            [
                'First Name:',
                'first_name',
                'uc_words',
            ],
            ['Last Name:', 'last_name', 'uc_words'],
            ['Visit Date:', 'visit_date', 'date'],
            ['Medical Record #:', 'mrn', 'int'],
            ['Address:', 'address'],
            ['Date of Birth:', 'dob', 'date'],
            ['Sex:', 'sex'],
            ['Phones:', 'phones', ['Home:', 'Cell:', 'Other:']],
            ['Dx:', 'conditions'],
            ['Active Problems:', 'instructions'],
            ['Services Ordered:', 'chargeable_services'],
        ];

        $currentCheckpoint = 0;
        for ($n = 0; $n <= count($array); $n++) {
            //check if string is empty
            if ( ! isset($array[$n])) {
                //can i do this?
                break;
            }

            $string = $array[$n];

            if (empty(trim($string))) {
                //can i do this?
                continue;
            }

            $checkpoint = $checkpoints[$currentCheckpoint];

            //add key as name
            $checkpointKey = $checkpoint[0];

            //remove
            if (str_contains($string, $checkpointKey)) {
                $string = trim(str_replace($checkpointKey, ' ', $string));

                if (empty($string)) {
                    continue;
                }
            }
            // checkpoints will be the set sections - to know where to put the variable data. At the end or start of the loop recognize if we are going to next checkpoint by checking the next?

            //concat later? merge strings? (give other key) basically make all items arrays that contain all the values at the first level
            $carePlan[$checkpoint[1]][] = $string;

            $nextCheckpoint = $currentCheckpoint + 1;
            if (isset($array[$n + 1]) && isset($checkpoints[$nextCheckpoint])) {
                if (str_contains($array[$n + 1], $checkpoints[$nextCheckpoint][0])) {
                    $currentCheckpoint = $nextCheckpoint;
                }
            }

            //then we talk about transforming, recognizing categorization and validating within a certain section

            //when removing checkpoint key, trim string, check if empty, and proceed
        }

        $this->carePlan = $carePlan;

        $x = 1;

        return $this;
    }

    private function categorize()
    {


        return $this->string;

    }

    private function prepareField($field)
    {
        return ucwords(strtolower(trim($field)));
    }

}
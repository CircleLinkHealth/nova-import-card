<?php
/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\OCR;


use Carbon\Carbon;
use setasign\Fpdi\Fpdi;
use Spatie\PdfToText\Pdf;


class UPGPdfCarePlan
{
    protected $fileName;

    protected $processedFileName;

    protected $string;

    protected $carePlan = [];

    protected $checkpoints;

    protected $currentCheckpoint = 0;

    protected $count = 0;

    protected $array;

    public function __construct($fileName)
    {
        $this->fileName    = $fileName;
        $this->checkpoints = $this->setCheckpoints();
    }

    protected function setCheckpoints()
    {
        return [
            [
                'search'   => 'First Name:',
                'key'      => 'first_name',
                'callback' => function ($string) {
                    $this->carePlan['first_name'] = ucwords(strtolower($string));

                    return true;
                },
            ],
            [
                'search'   => 'Last Name:',
                'key'      => 'last_name',
                'callback' => function ($string) {
                    $this->carePlan['last_name'] = ucwords(strtolower($string));
                },
            ],
            [
                'search'   => 'Visit Date:',
                'key'      => 'visit_date',
                'callback' => function ($string) {
                    $this->carePlan['visit_date'] = Carbon::parse($string);
                },
            ],
            [
                'search'   => 'Medical Record #:',
                'key'      => 'mrn',
                'callback' => function ($string) {
                    $this->carePlan['mrn'] = (int)$string;
                },
            ],
            [
                'search' => 'Address:',
                'key'    => 'address',
            ],
            [
                'search'   => 'Date of Birth:',
                'key'      => 'dob',
                'callback' => function ($string) {
                    $this->carePlan['dob'] = Carbon::parse($string);
                },
            ],
            [
                'search' => 'Sex:',
                'key'    => 'sex',
            ],
            [
                'search' => 'Phones:',
                'key'    => 'phones',
                //                'callback' => ['Home:', 'Cell:', 'Other:'],
            ],
            [
                'search' => 'Dx:',
                'key'    => 'conditions',
            ],
            [
                'search'   => 'Active Problems:',
                'key'      => 'instructions',
                'callback' => function ($string) {

                    if (str_contains(strtolower($string), 'recommendations:') || str_contains(strtolower($string), 'care plan')){
                        $this->carePlan['instructions'][]= ['condition' => $this->array[$this->count - 1]];
                        return;
                    }
                    if ( ! isset($this->carePlan['instructions']) && empty($this->carePlan['instructions'])) {
                        return;
                    }
                    if (in_array($string, $this->carePlan['conditions'])){
                        return;
                    }
                    $this->carePlan['instructions'][count($this->carePlan['instructions']) - 1][] = $string;
                },
            ],
            [
                'search' => 'Services Ordered:',
                'key'    => 'chargeable_services',
            ],
        ];
    }

    public function read()
    {
        return $this
            ->getText()
            ->parseString();
    }

    private function getText()
    {
        $pdf = new Fpdi();

        $pageCount = $pdf->setSourceFile(storage_path($this->fileName));
        $pdf->AddPage();

        for ($n = 1; $n <= $pageCount; $n++) {
            $tplId = $pdf->importPage($n);

            //crop pdf
            $pdf->useTemplate($tplId, 0, -60, 210, 400);
            $pdf->AddPage();
        }

        $this->processedFileName = preg_replace('/\\.[^.\\s]{3,4}$/', '', $this->fileName . '_processed.pdf');
        $pdf->Output(storage_path($this->processedFileName), 'F');

        //set .env var and config var
        $this->string = Pdf::getText(storage_path($this->processedFileName), '/usr/local/bin/pdftotext',
            ['layout', 'nopgbrk']);

        return $this;
    }

    private function parseString()
    {
        $this->array = collect(preg_split("/[\n]/", $this->string))->filter()->values()->all();

        while ($this->count <= count($this->array)) {
            if (! isset($this->array[$this->count])){
                break;
            }

            $checkpoint = $this->checkpoints[$this->currentCheckpoint];

            $search = $checkpoint['search'];


            $string = $this->array[$this->count];
            if (str_contains($string, $search)) {
                $string = trim(str_replace($search, ' ', $string));
                if (empty($string)) {
                    $this->count++;
                    continue;
                }
            }

            if (isset($checkpoint['callback']) && ! empty($checkpoint['callback'])) {
                $checkpoint['callback']($string);
            } else {
                $this->carePlan[$checkpoint['key']][] = $string;
            }

            //concat later? merge strings? (give other key) basically make all items arrays that contain all the values at the first level

            $nextCheckpoint = $this->currentCheckpoint + 1;
            if (isset($this->array[$this->count + 1]) && isset($this->checkpoints[$nextCheckpoint])) {
                if (str_contains($this->array[$this->count + 1], $this->checkpoints[$nextCheckpoint]['search'])) {
                    $this->currentCheckpoint = $nextCheckpoint;
                }
            }

            $this->count++;
        }

        unlink(storage_path($this->processedFileName));


        return $this->carePlan;
    }
}
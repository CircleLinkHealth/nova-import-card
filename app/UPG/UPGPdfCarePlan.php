<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\UPG;

use App\UPG\ValueObjects\PdfCarePlan;
use Carbon\Carbon;
use Illuminate\Support\Str;
use setasign\Fpdi\Fpdi;
use Spatie\PdfToText\Pdf;

class UPGPdfCarePlan
{
    protected $array;

    protected $carePlan = [];

    protected $checkpoints;

    protected $count = 0;

    protected $currentCheckpoint = 0;
    protected $fileName;

    protected $processedFileName;

    protected $string;

    public function __construct($fileName)
    {
        $this->fileName    = $fileName;
        $this->checkpoints = $this->setCheckpoints();
    }

    public function read()
    {
        return $this
            ->getText()
            ->parseString();
    }

    /**
     * Checkpoints are sections of the pdf. The system will read each string of the pdf and perform operations depending on which section (marked by a checkpoint) is reading.
     *
     * @return array
     */
    protected function setCheckpoints()
    {
        return [
            [
                'search' => [
                    'First Name:',
                ],
                'key'      => 'first_name',
                'callback' => function ($string) {
                    if ( ! empty($string)) {
                        $this->carePlan['first_name'] = ucwords(strtolower($string));
                    }
                },
            ],
            [
                'search' => [
                    'Last Name:',
                ],
                'key'      => 'last_name',
                'callback' => function ($string) {
                    if ( ! empty($string)) {
                        $this->carePlan['last_name'] = ucwords(strtolower($string));
                    }
                },
            ],
            [
                'search' => [
                    'Visit Date:',
                ],
                'key'      => 'visit_date',
                'callback' => function ($string) {
                    if ( ! empty($string)) {
                        $this->carePlan['visit_date'] = Carbon::parse($string);
                    }
                },
            ],
            [
                'search' => [
                    'Medical Record #:',
                ],
                'key'      => 'mrn',
                'callback' => function ($string) {
                    if ( ! empty($string)) {
                        $this->carePlan['mrn'] = $string;
                    }
                },
            ],
            [
                'search' => [
                    'Address:',
                ],
                'key' => 'address',
            ],
            [
                'search' => [
                    'Date of Birth:',
                ],
                'key'      => 'dob',
                'callback' => function ($string) {
                    if ( ! empty($string)) {
                        $this->carePlan['dob'] = Carbon::parse($string);
                    }
                },
            ],
            [
                'search' => [
                    'Sex:',
                ],
                'key'      => 'sex',
                'callback' => function ($string) {
                    if ( ! empty($string)) {
                        $this->carePlan['sex'] = strtolower($string);
                    }
                },
            ],
            [
                'search' => [
                    'Phones:',
                ],
                'key' => 'phones',
            ],
            [
                'search' => [
                    'Dx:',
                ],
                'key' => 'problems',
            ],
            [
                'search' => [
                    'Active Problems:',
                    'Plan:',
                ],
                'key'      => 'instructions',
                'callback' => function ($string) {
                    //Usually actual instructions exist below a string containing recommendations and/or care plan, and the name of the condition is above that
                    if (Str::contains(strtolower($string), 'recommendations:') || Str::contains(strtolower($string), 'care plan')) {
                        $this->carePlan['instructions'][] = ['problem_name' => $this->array[$this->count - 1]];

                        return;
                    }
                    //exclude everything before tha actual listing of the instructions
                    //todo: how do deal with **onset diabetes
                    if ( ! isset($this->carePlan['instructions']) && empty($this->carePlan['instructions'])) {
                        return;
                    }
                    //skip the line where the name if the condition is listed
                    if (in_array($string, $this->carePlan['problems'])) {
                        return;
                    }
                    //store string in the latest instruction
                    $this->carePlan['instructions'][count($this->carePlan['instructions']) - 1]['value'][] = $string;
                },
            ],
            [
                'search' => [
                    'Services Ordered:',
                ],
                'key' => 'chargeable_services',
            ],
        ];
    }

    private function getText()
    {
        $pdf = new Fpdi();

        $pageCount = $pdf->setSourceFile(storage_path($this->fileName));

        //Create a croped version of each page of the pdf, in order to exclude header and footer
        $pdf->AddPage();

        for ($n = 1; $n <= $pageCount; ++$n) {
            $tplId = $pdf->importPage($n);

            //crop pdf
            $pdf->useTemplate($tplId, 0, -50, 210, 380);
            $pdf->AddPage();
        }

        $this->processedFileName = preg_replace('/\\.[^.\\s]{3,4}$/', '', $this->fileName.'_processed.pdf');

        //save cropped pdf
        $pdf->Output(storage_path($this->processedFileName), 'F');

        //read cropped pdf
        $this->string = Pdf::getText(
            storage_path($this->processedFileName),
            config('pdftotext.path'),
            ['layout', 'nopgbrk']
        );

        return $this;
    }

    private function parseString()
    {
        $this->array = collect(preg_split("/[\n]/", $this->string))->values()->all();

        while ($this->count < count($this->array)) {
            $checkpoint = $this->checkpoints[$this->currentCheckpoint];

            $searches = $checkpoint['search'];

            $string = $this->array[$this->count];

            //if the search term exists in the string remove it. If nothing is left after that, get next string
            foreach ($searches as $search) {
                if (Str::contains($string, $search)) {
                    $string = trim(str_replace($search, ' ', $string));
                }
            }

            //perform callback if it exists in the section, else just store the string
            if (isset($checkpoint['callback']) && ! empty($checkpoint['callback'])) {
                $checkpoint['callback']($string);
            } else {
                if ( ! empty($string)) {
                    $this->carePlan[$checkpoint['key']][] = $string;
                }
            }

            //check next string, to see if we have reached the next checkpoint
            $nextCheckpoint = $this->currentCheckpoint + 1;
            if (isset($this->array[$this->count + 1], $this->checkpoints[$nextCheckpoint])) {
                foreach ($this->checkpoints[$nextCheckpoint]['search'] as $search) {
                    if (Str::contains($this->array[$this->count + 1], $search)) {
                        $this->currentCheckpoint = $nextCheckpoint;
                    }
                }
            }

            ++$this->count;
        }

        //delete processed file
        unlink(storage_path($this->processedFileName));

        return new PdfCarePlan($this->carePlan);
    }
}

<?php

namespace App\Jobs;

use App\Services\CCD\ProcessEligibilityService;
use App\Services\Eligibility\Csv\CsvPatientList;
use App\Traits\ValidatesEligibility;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\MessageBag;
use Storage;

class GenerateEligibilityBatchesForReportWriter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ValidatesEligibility;

    protected $user;

    protected $files;

    protected $practiceId;

    protected $filterProblems;

    protected $filterInsurance;

    protected $filterLastEncounter;

    protected $service;

    protected $jsonValidationStats = [
        'total'             => 0,
        'invalid_data'      => 0,
        'mrn'               => 0,
        'name'              => 0,
        'dob'               => 0,
        'problems'          => 0,
        'phones'            => 0,
    ];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, Array $files, $practiceId, $filterProblems, $filterInsurance, $filterLastEncounter)
    {
        $this->user = $user;
        $this->files = $files;
        $this->practiceId = $practiceId;
        $this->filterProblems = $filterProblems;
        $this->filterInsurances = $filterInsurance;
        $this->filterLastEncounter = $filterLastEncounter;
        $this->service = new ProcessEligibilityService();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->files as $file) {
            $invalidStructure = 0;
            $errors = [];
            if ($file['ext'] == 'csv') {
                //add try
                $string         = Storage::disk('google')->get($file['path']);
                $patients       = $this->parseCsvStringToArray($string);
                $csvPatientList = new CsvPatientList(collect($patients));
                $isValid        = $csvPatientList->guessValidator();
                if ( ! $isValid) {
                    $invalidStructure = 1;
                }

                $batch = $this->service->createSingleCSVBatch($patients, $this->practiceId, $this->filterLastEncounter, $this->filterInsurance,
                    $this->filterProblems, $invalidStructure);
                if ($batch) {
                    $messages['success'][] = "Eligibility Batch created.";
                }
                //delete files that had batches created for them?
            }
            if ($file['ext'] == 'json') {
                //try
                $string = Storage::disk('google')->get($file['path']);
                if ( ! is_json($string)) {
                    //todo: what to do here
                    continue;
                }

                $data = json_decode($string, true);

                foreach ($data as $patient) {
                    $this->jsonValidationStats['total'] += 1;
                    $structureValidator = $this->validateJsonStructure($patient);
                    if ($structureValidator->fails()){
                        $invalidStructure += 1;
                    }
                    $dataValidator = $this->validateRow($patient);
                    $this->calculateJsonValidationStats($dataValidator->errors());
                }
                $batch = $this->service->createClhMedicalRecordTemplateBatch($this->user->ehrReportWriterInfo->google_drive_folder_path,
                    $file['name'], $this->practiceId, $this->filterLastEncounter, $this->filterInsurance,
                    $this->filterProblems, $invalidStructure, $this->jsonValidationStats);
            }
        }
        //Delete files on drive? Or check each time if a batch exists?
    }
    private function parseCsvStringToArray($string)
    {
        $lines   = explode(PHP_EOL, $string);
        $headers = str_getcsv(array_shift($lines));
        $data    = [];
        foreach ($lines as $line) {
            $row = [];
            foreach (str_getcsv($line) as $key => $field) {
                $row[$headers[$key]] = $field;
            }
            $row    = array_filter($row);
            $data[] = $row;
        }

        return $data;
    }

    private function calculateJsonValidationStats(MessageBag $errors){

        if (! empty($errors)) {
            $this->jsonValidationStats['invalid_data'] += 1;
            if (array_key_exists('mrn', $errors->messages())) {
                $this->jsonValidationStats['mrn'] += 1;
            }

            if (array_key_exists('first_name', $errors->messages()) || array_key_exists('last_name', $errors->messages())) {
                $this->jsonValidationStats['name'] += 1;
            }

            if (array_key_exists('dob', $errors->messages())) {
                $this->jsonValidationStats['dob'] += 1;
            }
            if (array_key_exists('problems', $errors->messages())) {
                $this->jsonValidationStats['problems'] += 1;
            }
            if (array_key_exists('phones', $errors->messages())) {
                $this->jsonValidationStats['phones'] += 1;
            }
        }
    }
}

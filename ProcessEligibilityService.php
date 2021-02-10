<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility;

use CircleLinkHealth\Core\Exceptions\FileNotFoundException;
use CircleLinkHealth\Core\GoogleDrive;
use CircleLinkHealth\Eligibility\DTO\CsvPatientList;
use CircleLinkHealth\Eligibility\Exceptions\CsvEligibilityListStructureValidationException;
use CircleLinkHealth\Eligibility\Jobs\ProcessSinglePatientEligibility;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Loggers\NumberedAllergyFields;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Loggers\NumberedMedicationFields;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Loggers\NumberedProblemFields;
use CircleLinkHealth\Eligibility\Notifications\EligibilityBatchProcessed;
use CircleLinkHealth\SharedModels\Entities\EligibilityBatch;
use CircleLinkHealth\SharedModels\Entities\EligibilityJob;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Support\Facades\Storage;

class ProcessEligibilityService
{
    use ValidatesEligibility;

    /**
     * @param $type
     * @param array $options
     *
     * @return \CircleLinkHealth\SharedModels\Entities\EligibilityBatch
     */
    public function createBatch($type, int $practiceId, $options = [])
    {
        return EligibilityBatch::create(
            [
                'type'        => $type,
                'practice_id' => $practiceId,
                'status'      => EligibilityBatch::STATUSES['not_ready_to_start'],
                'options'     => $options,
            ]
        );
    }

    /**
     * @param $folder
     * @param $fileName
     * @param $filterLastEncounter
     * @param $filterInsurance
     * @param $filterProblems
     * @param bool $finishedReadingFile
     * @param null $filePath
     *
     * @return \Illuminate\Database\Eloquent\Model|ProcessEligibilityService
     */
    public function createClhMedicalRecordTemplateBatch(
        $folder,
        $fileName,
        int $practiceId,
        $filterLastEncounter,
        $filterInsurance,
        $filterProblems,
        $finishedReadingFile = false,
        $filePath = null
    ) {
        return $this->createBatch(
            EligibilityBatch::CLH_MEDICAL_RECORD_TEMPLATE,
            $practiceId,
            [
                'folder'              => $folder,
                'fileName'            => $fileName,
                'filePath'            => $filePath,
                'filterLastEncounter' => (bool) $filterLastEncounter,
                'filterInsurance'     => (bool) $filterInsurance,
                'filterProblems'      => (bool) $filterProblems,
                'finishedReadingFile' => (bool) $finishedReadingFile,
                //did the system read all lines from the file and create eligibility jobs?
            ]
        );
    }

    /**
     * @param $patientListCsvFilePath
     *
     * @throws \Exception
     *
     * @return array
     */
    public function createEligibilityJobFromCsvBatch(EligibilityBatch $batch, $patientListCsvFilePath)
    {
        if ( ! file_exists($patientListCsvFilePath)) {
            throw new FileNotFoundException();
        }

        return iterateCsv(
            $patientListCsvFilePath,
            function ($row) use ($batch) {
                $csvPatientList = new CsvPatientList(collect([$row]));
                $isValid = $csvPatientList->guessValidatorAndValidate();

                if ( ! $isValid) {
                    return [
                        'error' => 'This csv does not match any of the supported templates. you can see supported templates here https://drive.google.com/drive/folders/1zpiBkegqjTioZGzdoPqZQAqWvXkaKEgB',
                    ];
                }

                return $this->createEligibilityJobFromCsvRow($row, $batch);
            }
        );
    }

    /**
     * @return \CircleLinkHealth\SharedModels\Entities\EligibilityJob|\Illuminate\Database\Eloquent\Model
     */
    public function createEligibilityJobFromCsvRow(array $patient, EligibilityBatch $batch)
    {
        $patient = $this->transformCsvRow($patient);

        $validator = $this->validateRow($patient);

        $mrn = $patient['mrn'] ?? $patient['mrn_number'] ?? $patient['patient_id'];

        $hash = $batch->practice->name.$patient['first_name'].$patient['last_name'].$mrn;

        return EligibilityJob::create(
            [
                'batch_id' => $batch->id,
                'hash'     => $hash,
                'data'     => $patient,
                'errors'   => $validator->fails()
                    ? $validator->errors()
                    : null,
            ]
        );
    }

    /**
     * @param $dir
     * @param $filterLastEncounter
     * @param $filterInsurance
     * @param $filterProblems
     *
     * @return $this|\Illuminate\Database\Eloquent\Model
     */
    public function createGoogleDriveCcdsBatch(
        $dir,
        int $practiceId,
        $filterLastEncounter,
        $filterInsurance,
        $filterProblems
    ) {
        return $this->createBatch(
            EligibilityBatch::TYPE_GOOGLE_DRIVE_CCDS,
            $practiceId,
            [
                'dir'                 => $dir,
                'filterLastEncounter' => (bool) $filterLastEncounter,
                'filterInsurance'     => (bool) $filterInsurance,
                'filterProblems'      => (bool) $filterProblems,
            ]
        );
    }

    /**
     * @param $filterLastEncounter
     * @param $filterInsurance
     * @param $filterProblems
     *
     * @return \CircleLinkHealth\SharedModels\Entities\EligibilityBatch
     */
    public function createSingleCSVBatch(
        int $practiceId,
        $filterLastEncounter,
        $filterInsurance,
        $filterProblems
    ) {
        return $this->createBatch(
            EligibilityBatch::TYPE_ONE_CSV,
            $practiceId,
            [
                //SAVING patientList has been DEPRECATED on Jan 11 2019
                'patientList'         => [],
                'filterLastEncounter' => (bool) $filterLastEncounter,
                'filterInsurance'     => (bool) $filterInsurance,
                'filterProblems'      => (bool) $filterProblems,
            ]
        );
    }

    /**
     * @param $folder
     * @param $fileName
     * @param $filterLastEncounter
     * @param $filterInsurance
     * @param $filterProblems
     * @param null $filePath
     *
     * @return \Illuminate\Database\Eloquent\Model|ProcessEligibilityService
     */
    public function createSingleCSVBatchFromGoogleDrive(
        $folder,
        $fileName,
        int $practiceId,
        $filterLastEncounter,
        $filterInsurance,
        $filterProblems,
        $filePath = null
    ) {
        return $this->createBatch(
            EligibilityBatch::TYPE_ONE_CSV,
            $practiceId,
            [
                'folder'   => $folder,
                'fileName' => $fileName,
                'filePath' => $filePath,
                //did the system read all lines from the file and create eligibility jobs?
                'finishedReadingFile' => false,
                'filterLastEncounter' => (bool) $filterLastEncounter,
                'filterInsurance'     => (bool) $filterInsurance,
                'filterProblems'      => (bool) $filterProblems,
            ]
        );
    }

    /**
     * Edit the details of the eligibility batch.
     *
     * @param array $options
     *
     * @return \CircleLinkHealth\SharedModels\Entities\EligibilityBatch
     */
    public function editBatch(EligibilityBatch $batch, $options = [])
    {
        $updated = $batch->update(
            [
                'status'  => EligibilityBatch::STATUSES['not_started'],
                'options' => $options,
            ]
        );

        return $batch->fresh();
    }
    
    public function notify(EligibilityBatch $batch)
    {
        if (isProductionEnv()) {
            sendSlackMessage(
                '#parse_enroll_import',
                "Hey I just processed this list, it's crazy. Here's some patients, call them maybe? {$batch->linkToView()}"
            );
        }

        optional($batch->initiatorUser)->notify(new EligibilityBatchProcessed($batch));
    }

    /**
     * Store updated EligibilityBatch details for reprocessing. Delete existing EligibilityJobs if option `reprocess
     * from scratch` was chosen.
     *
     * @param $folder
     * @param $fileName
     * @param $filterLastEncounter
     * @param $filterInsurance
     * @param $filterProblems
     * @param $reprocessingMethod
     *
     * @return \CircleLinkHealth\SharedModels\Entities\EligibilityBatch
     */
    public function prepareClhMedicalRecordTemplateBatchForReprocessing(
        EligibilityBatch $batch,
        $folder,
        $fileName,
        $filterLastEncounter,
        $filterInsurance,
        $filterProblems,
        $reprocessingMethod
    ) {
        $batch = $this->editBatch(
            $batch,
            [
                'folder'              => $folder,
                'fileName'            => $fileName,
                'filterLastEncounter' => (bool) $filterLastEncounter,
                'filterInsurance'     => (bool) $filterInsurance,
                'filterProblems'      => (bool) $filterProblems,
                //did the system read all lines from the file and create eligibility jobs?
                //reset to false so that system will read the file again
                'finishedReadingFile' => false,
                'reprocessingMethod'  => $reprocessingMethod,
            ]
        );

        if (EligibilityBatch::REPROCESS_FROM_SCRATCH == $reprocessingMethod) {
            $deletedJobs = EligibilityJob::whereBatchId($batch->id)
                ->forceDelete();

            $deletedEnrollees = Enrollee::whereBatchId($batch->id)
                ->delete();
        }

        return $batch;
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    public function processGoogleDriveCsvForEligibility(EligibilityBatch $batch)
    {
        $batch->loadMissing('practice');

        $driveFolder   = $batch->options['folder'];
        $driveFileName = $batch->options['fileName'];
        $driveFilePath = $batch->options['filePath'] ?? null;

        $driveHandler = new GoogleDrive();

        try {
            $stream = $driveHandler
                ->getFileStream($driveFileName, $driveFolder);
        } catch (\Exception $e) {
            \Log::error("EXCEPTION `{$e->getMessage()}`");
            $batch->status = EligibilityBatch::STATUSES['error'];
            $batch->save();

            return null;
        }
        $localDisk = Storage::disk('local');

        $fileName   = "eligibl_{$driveFileName}";
        $pathToFile = storage_path("app/${fileName}");

        $savedLocally = $localDisk->put($fileName, $stream);

        if ( ! $savedLocally) {
            throw new \Exception("Failed saving ${pathToFile}");
        }

        try {
            \Log::info(
                "BEGIN creating eligibility jobs from csv file in google drive: [`folder => ${driveFolder}`, `filename => ${driveFileName}`]"
            );

            $iterator = read_file_using_generator($pathToFile);

            $headers = [];

            $i = 1;
            foreach ($iterator as $iteration) {
                if ( ! $iteration) {
                    continue;
                }
                if (1 == $i) {
                    $headers = str_getcsv($iteration, ',');
                    $this->throwExceptionIfStructureErrors($headers, $batch);
                    ++$i;
                    continue;
                }
                $row = [];
                foreach (str_getcsv($iteration) as $key => $field) {
                    try {
                        if (array_key_exists($key, $headers)) {
                            $headerName = $headers[$key];
                        }

                        if (isset($headerName)) {
                            $row[$headerName] = $field;
                        }
                    } catch (\Exception $exception) {
                        \Log::error(
                            $exception->getMessage(),
                            [
                                'trace'        => $exception->getTrace(),
                                'batch_id_tag' => "batch_id:$batch->id",
                            ]
                        );

                        continue;
                    }
                }
                $row = array_filter($row);

                if ( ! is_array($row) || empty($row)) {
                    continue;
                }

                $patient = sanitize_array_keys($this->transformCsvRow($row));

                //we do this to use the data transformation the method performs
                $validator = $this->validateRow($patient);

                $mrn = $patient['mrn'] ?? $patient['mrn_number'] ?? $patient['patient_id'] ?? $patient['dob'];

                $hash = $batch->practice->name.$patient['first_name'].$patient['last_name'].$mrn;

                $job = EligibilityJob::updateOrCreate(
                    [
                        'batch_id' => $batch->id,
                        'hash'     => $hash,
                    ],
                    [
                        'data'   => $patient,
                        'errors' => $validator->fails()
                            ? $validator->errors()
                            : null,
                    ]
                );

                ProcessSinglePatientEligibility::dispatch($job->id);
            }

            \Log::info(
                "FINISH creating eligibility jobs from csv file in google drive: [`folder => ${driveFolder}`, `filename => ${driveFileName}`]"
            );

            $mem = format_bytes(memory_get_peak_usage());

            \Log::info("BEGIN deleting `${fileName}`");
            $deleted = $localDisk->delete($fileName);
            \Log::info("FINISH deleting `${fileName}`");

            \Log::info("memory_get_peak_usage: ${mem}");

            $options                        = $batch->options;
            $options['finishedReadingFile'] = true;
            $batch->options                 = $options;
            $batch->save();

            $initiator = $batch->initiatorUser()->firstOrFail();
            if ($initiator->hasRole('ehr-report-writer') && $initiator->ehrReportWriterInfo) {
                Storage::drive('google')->move($driveFilePath, "{$driveFolder}/processed_{$driveFileName}");
            }
        } catch (\Exception $e) {
            \Log::info("EXCEPTION `{$e->getMessage()}`");

            \Log::info("BEGIN deleting `${fileName}`");
            $deleted = $localDisk->delete($fileName);
            \Log::info("FINISH deleting `${fileName}`");

            throw $e;
        }
    }

    private function throwExceptionIfStructureErrors(array $headings, EligibilityBatch $batch)
    {
        $patient = array_flip($headings);

        $csvPatientList = new CsvPatientList(collect([$patient]));
        $isValid        = $csvPatientList->guessValidatorAndValidate() ?? null;

        $errors = [];
        if ( ! $isValid) {
            $errors[] = $this->validateRow($patient)->errors()->keys();
        }

        if ( ! empty($errors)) {
            $options                        = $batch->options;
            $options['errorsReadingSource'] = $errors;
            $batch->options                 = $options;
            $batch->save();

            throw new CsvEligibilityListStructureValidationException($batch, $errors);
        }
    }

    /**
     * @param $patient
     *
     * @return mixed
     */
    private function transformCsvRow($patient)
    {
        if (count(preg_grep('/^problem_[\d]*/', array_keys($patient))) > 0) {
            $problems = (new NumberedProblemFields())->handle($patient);

            $patient['problems_string'] = json_encode(
                [
                    'Problems' => $problems,
                ]
            );
        }

        if (count(preg_grep('/^medication_[\d]*/', array_keys($patient))) > 0) {
            $medications = (new NumberedMedicationFields())->handle($patient);

            $patient['medications_string'] = json_encode(
                [
                    'Medications' => $medications,
                ]
            );
        }

        if (count(preg_grep('/^allergy_[\d]*/', array_keys($patient))) > 0) {
            $allergies = (new NumberedAllergyFields())->handle($patient);

            $patient['allergies_string'] = json_encode(
                [
                    'Allergies' => $allergies,
                ]
            );
        }

        return $patient;
    }
}

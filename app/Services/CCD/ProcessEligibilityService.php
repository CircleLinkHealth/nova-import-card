<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\CCD;

use App\EligibilityBatch;
use App\EligibilityJob;
use App\Enrollee;
use App\Exceptions\FileNotFoundException;
use App\Importer\Loggers\Allergy\NumberedAllergyFields;
use App\Importer\Loggers\Medication\NumberedMedicationFields;
use App\Importer\Loggers\Problem\NumberedProblemFields;
use App\Jobs\CheckCcdaEnrollmentEligibility;
use App\Jobs\ProcessCcda;
use App\Jobs\ProcessCcdaFromGoogleDrive;
use App\Jobs\ProcessEligibilityFromGoogleDrive;
use App\Models\MedicalRecords\Ccda;
use App\Services\Eligibility\Csv\CsvPatientList;
use App\Services\GoogleDrive;
use App\Traits\ValidatesEligibility;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Support\Facades\Storage;

class ProcessEligibilityService
{
    use ValidatesEligibility;

    /**
     * @param $type
     * @param array $options
     *
     * @return EligibilityBatch
     */
    public function createBatch($type, int $practiceId, $options = [])
    {
        return EligibilityBatch::create([
            'type'        => $type,
            'practice_id' => $practiceId,
            'status'      => EligibilityBatch::STATUSES['not_started'],
            'options'     => $options,
        ]);
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
        return $this->createBatch(EligibilityBatch::CLH_MEDICAL_RECORD_TEMPLATE, $practiceId, [
            'folder'              => $folder,
            'fileName'            => $fileName,
            'filePath'            => $filePath,
            'filterLastEncounter' => (bool) $filterLastEncounter,
            'filterInsurance'     => (bool) $filterInsurance,
            'filterProblems'      => (bool) $filterProblems,
            'finishedReadingFile' => (bool) $finishedReadingFile,
            //did the system read all lines from the file and create eligibility jobs?
        ]);
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
     * @return EligibilityJob|\Illuminate\Database\Eloquent\Model
     */
    public function createEligibilityJobFromCsvRow(array $patient, EligibilityBatch $batch)
    {
        $patient = $this->transformCsvRow($patient);

        $validator = $this->validateRow($patient);

        $hash = $batch->practice->name.$patient['first_name'].$patient['last_name'].$patient['mrn'];

        return EligibilityJob::create([
            'batch_id' => $batch->id,
            'hash'     => $hash,
            'data'     => $patient,
            'errors'   => $validator->fails()
                ? $validator->errors()
                : null,
        ]);
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
        return $this->createBatch(EligibilityBatch::TYPE_GOOGLE_DRIVE_CCDS, $practiceId, [
            'dir'                 => $dir,
            'filterLastEncounter' => (bool) $filterLastEncounter,
            'filterInsurance'     => (bool) $filterInsurance,
            'filterProblems'      => (bool) $filterProblems,
        ]);
    }

    /**
     * @return $this|\Illuminate\Database\Eloquent\Model
     */
    public function createPhoenixHeartBatch()
    {
        return $this->createBatch(
            EligibilityBatch::TYPE_PHX_DB_TABLES,
            Practice::whereName('phoenix-heart')->firstOrFail()->id,
            [
                'filterLastEncounter' => false,
                'filterInsurance'     => true,
                'filterProblems'      => true,
            ]
        );
    }

    /**
     * @param $filterLastEncounter
     * @param $filterInsurance
     * @param $filterProblems
     *
     * @return EligibilityBatch
     */
    public function createSingleCSVBatch(
        int $practiceId,
        $filterLastEncounter,
        $filterInsurance,
        $filterProblems
    ) {
        return $this->createBatch(EligibilityBatch::TYPE_ONE_CSV, $practiceId, [
            //SAVING patientList has been DEPRECATED on Jan 11 2019
            'patientList'         => [],
            'filterLastEncounter' => (bool) $filterLastEncounter,
            'filterInsurance'     => (bool) $filterInsurance,
            'filterProblems'      => (bool) $filterProblems,
        ]);
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
        return $this->createBatch(EligibilityBatch::TYPE_ONE_CSV, $practiceId, [
            'folder'              => $folder,
            'fileName'            => $fileName,
            'filePath'            => $filePath,
            'finishedReadingFile' => false, //did the system read all lines from the file and create eligibility jobs?
            'filterLastEncounter' => (bool) $filterLastEncounter,
            'filterInsurance'     => (bool) $filterInsurance,
            'filterProblems'      => (bool) $filterProblems,
        ]);
    }

    /**
     * Edit the details of the eligibility batch.
     *
     * @param array $options
     *
     * @return EligibilityBatch
     */
    public function editBatch(EligibilityBatch $batch, $options = [])
    {
        $updated = $batch->update([
            'status'  => EligibilityBatch::STATUSES['not_started'],
            'options' => $options,
        ]);

        return $batch->fresh();
    }

    public function fromGoogleDrive(EligibilityBatch $batch)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $cloudDisk = Storage::disk('google');
        $recursive = false; // Get subdirectories also?
        $dir       = $batch->options['dir'];

        $alreadyProcessed = Media::select('file_name')->whereModelType(Ccda::class)->whereIn('model_id', function ($query) use ($batch) {
            $query->select('id')
                ->from((new Ccda())->getTable())
                ->where('batch_id', $batch->id);
        })->distinct()->pluck('file_name');

        return collect($cloudDisk->listContents($dir, $recursive))
            ->where('type', '=', 'file')
            ->whereIn('mimetype', [
                'text/xml',
                'application/xml',
            ])
            ->whereNotIn('name', $alreadyProcessed->all())
            ->take(10)
            ->whenEmpty(function () {
                return false;
            })->whenNotEmpty(function ($collection) use ($batch) {
                $collection->each(function ($file) use (
                    $batch
                ) {
                    ProcessCcdaFromGoogleDrive::dispatch($file, $batch);
                });

                return true;
            });
    }

    public function handleAlreadyDownloadedZip(
        $dir,
        $practiceName,
        $filterLastEncounter,
        $filterInsurance,
        $filterProblems
    ) {
        $cloudDisk = Storage::disk('google');

        $practice  = Practice::whereName($practiceName)->firstOrFail();
        $recursive = false; // Get subdirectories also?
        $contents  = collect($cloudDisk->listContents($dir, $recursive));

        $processedDir = $contents->where('type', '=', 'dir')
            ->where('filename', '=', 'processed')
            ->first();

        if ( ! $processedDir) {
            $cloudDisk->makeDirectory("${dir}/processed");

            $processedDir = collect($cloudDisk->listContents($dir, $recursive))
                ->where('type', '=', 'dir')
                ->where('filename', '=', 'processed')
                ->first();
        }

        $zipFiles = $contents
            ->where('type', '=', 'file')
            ->where('mimetype', '=', 'application/zip')
            ->map(function ($file) use (
                $cloudDisk,
                $practice,
                $dir,
                $filterLastEncounter,
                $filterInsurance,
                $filterProblems,
                $processedDir
            ) {
                $localDisk = Storage::disk('local');

                $unzipDir = "zip/${dir}/unzipped";

                $cloudFilePath = $file['path'];
                $cloudFileName = $file['filename'];
                $cloudDirName = $file['dirname'];

                foreach ($localDisk->allFiles($unzipDir) as $path) {
                    if (str_contains($path, 'xml')) {
                        $now = Carbon::now()->toAtomString();
                        $randomStr = str_random();

                        $put = $cloudDisk->put(
                            "{$processedDir['path']}/${randomStr}-${now}",
                            fopen($localDisk->path($path), 'r+')
                        );
                        $ccda = Ccda::create([
                            'source'      => Ccda::GOOGLE_DRIVE."_${dir}",
                            'xml'         => stream_get_contents(fopen($localDisk->path($path), 'r+')),
                            'status'      => Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY,
                            'imported'    => false,
                            'practice_id' => (int) $practice->id,
                        ]);

                        //for some reason it doesn't save practice_id when using Ccda::create([])
                        $ccda->practice_id = (int) $practice->id;
                        $ccda->save();

                        ProcessCcda::withChain([
                            (new CheckCcdaEnrollmentEligibility(
                                $ccda->id,
                                $practice,
                                (bool) $filterLastEncounter,
                                (bool) $filterInsurance,
                                (bool) $filterProblems
                            ))->onQueue('low'),
                        ])->dispatch($ccda->id)
                            ->onQueue('low');
                    } else {
                        $pathWithUnderscores = str_replace('/', '_', $path);
                        $put = $cloudDisk->put(
                            "{$processedDir['path']}/${pathWithUnderscores}",
                            fopen($localDisk->path($path), 'r+')
                        );
                    }

                    $localDisk->delete($path);
                }
            });

        return true;
    }

    public function notifySlack($batch)
    {
        if (isProductionEnv()) {
            sendSlackMessage(
                ' #parse_enroll_import',
                "Hey I just processed this list, it's crazy. Here's some patients, call them maybe? {$batch->linkToView()}"
            );
        }
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
     * @return EligibilityBatch
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
        $batch = $this->editBatch($batch, [
            'folder'              => $folder,
            'fileName'            => $fileName,
            'filterLastEncounter' => (bool) $filterLastEncounter,
            'filterInsurance'     => (bool) $filterInsurance,
            'filterProblems'      => (bool) $filterProblems,
            //did the system read all lines from the file and create eligibility jobs?
            //reset to false so that system will read the file again
            'finishedReadingFile' => false,
            'reprocessingMethod'  => $reprocessingMethod,
        ]);

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
     * @return array|bool
     */
    public function processCsvForEligibility(EligibilityBatch $batch)
    {
        if (EligibilityBatch::TYPE_ONE_CSV != $batch->type) {
            throw new \Exception('$batch is not of type `'.EligibilityBatch::TYPE_ONE_CSV.'`.`');
        }

        $collection = collect($batch->options['patientList']);

        if ($collection->isEmpty()) {
            return false;
        }

        $patientList = [];

        for ($i = 1; $i <= 1000; ++$i) {
            $patient = $collection->shift();

            if ( ! is_array($patient)) {
                continue;
            }

            $patientList[] = $patient;

            $job = $this->createEligibilityJobFromCsvRow($patient, $batch);

            $patient['eligibility_job_id'] = $job->id;
        }

        $options                = $batch->options;
        $options['patientList'] = $collection->toArray();
        $batch->options         = $options;
        $batch->save();

        return $patientList;
    }

    /**
     * @throws \Exception
     * @throws \League\Flysystem\FileNotFoundException
     *
     * @return array
     */
    public function processGoogleDriveCsvForEligibility(EligibilityBatch $batch)
    {
        $driveFolder   = $batch->options['folder'];
        $driveFileName = $batch->options['fileName'];
        $driveFilePath = $batch->options['filePath'] ?? null;

        $driveHandler = new GoogleDrive();

        try {
            $stream = $driveHandler
                ->getFileStream($driveFileName, $driveFolder);
        } catch (\Exception $e) {
            \Log::info("EXCEPTION `{$e->getMessage()}`");
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
            \Log::info("BEGIN creating eligibility jobs from csv file in google drive: [`folder => ${driveFolder}`, `filename => ${driveFileName}`]");

            $iterator = read_file_using_generator($pathToFile);

            $headers = [];
            $data    = [];

            $i = 1;
            foreach ($iterator as $iteration) {
                if ( ! $iteration) {
                    continue;
                }
                if (1 == $i) {
                    $headers = str_getcsv($iteration, ',');
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
                        \Log::channel('logdna')->error($exception->getMessage(), [
                            'trace'        => $exception->getTrace(),
                            'batch_id_tag' => "batch_id:$batch->id",
                        ]);

                        continue;
                    }
                }
                $row    = array_filter($row);
                $data[] = $row;
            }

            $patientList = [];
            $data        = collect($data);
            for ($i = 1; $i <= 1000; ++$i) {
                $patient = $data->shift();

                if ( ! is_array($patient)) {
                    continue;
                }

                $patientList[] = $patient;

                $patient = $this->transformCsvRow($patient);

                $validator = $this->validateRow($patient);

                $hash = $batch->practice->name.$patient['first_name'].$patient['last_name'].$patient['mrn'];

                $job = EligibilityJob::create([
                    'batch_id' => $batch->id,
                    'hash'     => $hash,
                    'data'     => $patient,
                    'errors'   => $validator->fails()
                        ? $validator->errors()
                        : null,
                ]);

                $patient['eligibility_job_id'] = $job->id;
            }

            \Log::info("FINISH creating eligibility jobs from csv file in google drive: [`folder => ${driveFolder}`, `filename => ${driveFileName}`]");

            $mem = format_bytes(memory_get_peak_usage());

            \Log::info("BEGIN deleting `${fileName}`");
            $deleted = $localDisk->delete($fileName);
            \Log::info("FINISH deleting `${fileName}`");

            \Log::info("memory_get_peak_usage: ${mem}");

            $options                        = $batch->options;
            $options['patientList']         = $data->toArray();
            $options['finishedReadingFile'] = true;
            $batch->options                 = $options;
            $batch->save();

            $initiator = $batch->initiatorUser()->firstOrFail();
            if ($initiator->hasRole('ehr-report-writer') && $initiator->ehrReportWriterInfo) {
                Storage::drive('google')->move($driveFilePath, "{$driveFolder}/processed_{$driveFileName}");
            }

            return $patientList;
        } catch (\Exception $e) {
            \Log::info("EXCEPTION `{$e->getMessage()}`");

            \Log::info("BEGIN deleting `${fileName}`");
            $deleted = $localDisk->delete($fileName);
            \Log::info("FINISH deleting `${fileName}`");

            throw $e;
        }
    }

    public function queueFromGoogleDrive(EligibilityBatch $batch)
    {
        ProcessEligibilityFromGoogleDrive::dispatch($batch);
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

            $patient['problems_string'] = json_encode([
                'Problems' => $problems,
            ]);
        }

        if (count(preg_grep('/^medication_[\d]*/', array_keys($patient))) > 0) {
            $medications = (new NumberedMedicationFields())->handle($patient);

            $patient['medications_string'] = json_encode([
                'Medications' => $medications,
            ]);
        }

        if (count(preg_grep('/^allergy_[\d]*/', array_keys($patient))) > 0) {
            $allergies = (new NumberedAllergyFields())->handle($patient);

            $patient['allergies_string'] = json_encode([
                'Allergies' => $allergies,
            ]);
        }

        return $patient;
    }
}

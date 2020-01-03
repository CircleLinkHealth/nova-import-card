<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\EligibilityBatch;
use App\EligibilityJob;
use App\Jobs\MakePhoenixHeartWelcomeCallList;
use App\Jobs\ProcessSinglePatientEligibility;
use App\Models\PatientData\PhoenixHeart\PhoenixHeartName;
use App\Services\CCD\ProcessEligibilityService;
use App\Services\Eligibility\Adapters\JsonMedicalRecordAdapter;
use App\Services\GoogleDrive;
use App\TargetPatient;
use Illuminate\Console\Command;
use Storage;

class QueueEligibilityBatchForProcessing extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process an eligibility batch of CCDAs.';

    /**
     * @var ProcessEligibilityService
     */
    protected $processEligibilityService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:process';

    /**
     * Create a new command instance.
     */
    public function __construct(ProcessEligibilityService $processEligibilityService)
    {
        parent::__construct();
        $this->processEligibilityService = $processEligibilityService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $batch = $this->getBatch();

        if ( ! $batch) {
            return null;
        }

        switch ($batch->type) {
            case EligibilityBatch::TYPE_ONE_CSV:
                $batch = $this->queueSingleCsvJobs($batch);
                break;
            case EligibilityBatch::TYPE_GOOGLE_DRIVE_CCDS:
                $batch = $this->queueGoogleDriveJobs($batch);
                break;
            case EligibilityBatch::TYPE_PHX_DB_TABLES:
                $batch = $this->queuePHXJobs($batch);
                break;
            case EligibilityBatch::CLH_MEDICAL_RECORD_TEMPLATE:
                $batch = $this->queueClhMedicalRecordTemplateJobs($batch);
                break;
            case EligibilityBatch::ATHENA_API:
                $batch = $this->queueAthenaJobs($batch);
                break;
        }

        $this->afterProcessingHook($batch);
    }

    /**
     * Run this tasks after processing a batch.
     *
     * @param $batch
     */
    private function afterProcessingHook($batch)
    {
        if ($batch->isCompleted() && $batch->hasJobs()) {
            $this->processEligibilityService
                ->notifySlack($batch);
        }
    }

    /**
     * @throws \League\Flysystem\FileNotFoundException
     */
    private function createEligibilityJobsFromJsonFile(EligibilityBatch $batch): EligibilityBatch
    {
        $driveFolder   = $batch->options['folder'];
        $driveFileName = $batch->options['fileName'];
        $driveFilePath = $batch->options['filePath'] ?? null;

        $driveHandler = new GoogleDrive();
        try {
            $stream = $driveHandler
                ->getFileStream($driveFileName, $driveFolder);
        } catch (\Exception $e) {
            \Log::debug("EXCEPTION `{$e->getMessage()}`");
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
            \Log::debug("BEGIN creating eligibility jobs from json file in google drive: [`folder => ${driveFolder}`, `filename => ${driveFileName}`]");

            //Reading the file using a generator is expected to consume less memory.
            //implemented both to experiment
//            $this->readWithoutUsingGenerator($pathToFile, $batch);
            $this->readUsingGenerator($pathToFile, $batch);

            \Log::debug("FINISH creating eligibility jobs from json file in google drive: [`folder => ${driveFolder}`, `filename => ${driveFileName}`]");

            $mem = format_bytes(memory_get_peak_usage());

            \Log::debug("BEGIN deleting `${fileName}`");
            $deleted = $localDisk->delete($fileName);
            \Log::debug("FINISH deleting `${fileName}`");

            \Log::debug("memory_get_peak_usage: ${mem}");

            $options                        = $batch->options;
            $options['finishedReadingFile'] = true;
            $batch->options                 = $options;
            $batch->save();

            $initiator = $batch->initiatorUser()->firstOrFail();
            if ($initiator->hasRole('ehr-report-writer') && $initiator->ehrReportWriterInfo) {
                Storage::drive('google')->move($driveFilePath, "{$driveFolder}/processed_{$driveFileName}");
            }

            return $batch;
        } catch (\Exception $e) {
            \Log::debug("EXCEPTION `{$e->getMessage()}`");

            \Log::debug("BEGIN deleting `${fileName}`");
            $deleted = $localDisk->delete($fileName);
            \Log::debug("FINISH deleting `${fileName}`");

            throw $e;
        }
    }

    private function getBatch(): ?EligibilityBatch
    {
        return EligibilityBatch::where('status', '<', 2)
            ->with('practice')
            ->first();
    }

    private function queueAthenaJobs(EligibilityBatch $batch): EligibilityBatch
    {
        $query          = TargetPatient::whereBatchId($batch->id)->whereStatus(TargetPatient::STATUS_TO_PROCESS);
        $targetPatients = $query->with('batch')->chunkById(50, function ($targetPatients) use ($batch) {
            $batch->status = EligibilityBatch::STATUSES['processing'];
            $batch->save();

            $targetPatients->each(function (TargetPatient $targetPatient) use ($batch) {
                try {
                    $targetPatient->processEligibility();
                } catch (\Exception $exception) {
                    $targetPatient->status = TargetPatient::STATUS_ERROR;
                    $targetPatient->save();

                    throw $exception;
                }
            });
        });

        $batch->processPendingJobs(50);

        // Mark batch as processed by default
        $batch->status = EligibilityBatch::STATUSES['complete'];

        if ($query->exists() || EligibilityJob::whereBatchId($batch->id)->where('status', '<', 2)->exists()) {
            // Mark batch as processing if there are patients to precess in DB
            $batch->status = EligibilityBatch::STATUSES['processing'];
        }

        $batch->save();

        return $batch;
    }

    /**
     * @throws \League\Flysystem\FileNotFoundException
     */
    private function queueClhMedicalRecordTemplateJobs(EligibilityBatch $batch): EligibilityBatch
    {
        if ( ! (bool) $batch->options['finishedReadingFile']) {
            ini_set('memory_limit', '800M');

            $created = $this->createEligibilityJobsFromJsonFile($batch);
        }

        $batch->processPendingJobs(300);

        $jobsToBeProcessedCount = EligibilityJob::whereBatchId($batch->id)
            ->where('status', '<', 2)
            ->count();

        if (0 == $jobsToBeProcessedCount) {
            $batch->status = EligibilityBatch::STATUSES['complete'];
        } else {
            $batch->status = EligibilityBatch::STATUSES['processing'];
        }

        $batch->save();

        return $batch;
    }

    private function queueGoogleDriveJobs(EligibilityBatch $batch): EligibilityBatch
    {
        echo "\n queuing {$batch->id}";
        if ((int) $batch->status > 0 && $batch->updated_at->gt(now()->subMinutes(10))) {
            echo "\n bail. did nothing for {$batch->id}";
            echo "\n batch updated at {$batch->updated_at->toDateTimeString()}";

            return $batch;
        }

        $unprocessedCount = EligibilityJob::whereBatchId($batch->id)
            ->where('status', 0)
            ->count();

        echo "\n {$unprocessedCount} unprocessed records found";

        $batch->processPendingJobs();

        if (0 < $unprocessedCount) {
            echo "\n batch {$batch->id} has unprocessed ej that will be processed";

            return $batch;
        }

        if ( ! $batch->isFinishedFetchingFiles()) {
            echo "\n batch {$batch->id}: fetching CCDs from Drive";

            $result = $this->processEligibilityService->fromGoogleDrive($batch);

            if ($result) {
                $batch->status = EligibilityBatch::STATUSES['processing'];
                $batch->touch();

                return $batch;
            }
        }

        if (0 === $unprocessedCount) {
            $batch->status = EligibilityBatch::STATUSES['complete'];
            $batch->touch();

            return $batch;
        }

        $batch->status = EligibilityBatch::STATUSES['processing'];
        $batch->touch();

        return $batch;
    }

    /**
     * @param $batch
     */
    private function queuePHXJobs($batch): EligibilityBatch
    {
        $jobsToBeProcessedExist = EligibilityJob::whereBatchId($batch->id)
            ->where('status', '<', 2)
            ->exists();

        if ($jobsToBeProcessedExist) {
            $batch->status = EligibilityBatch::STATUSES['processing'];
        } elseif ( ! PhoenixHeartName::where('processed', '=', false)->exists()) {
            $batch->status = EligibilityBatch::STATUSES['complete'];
        }

        $batch->save();

        MakePhoenixHeartWelcomeCallList::dispatch($batch)
            ->onQueue('low');

        return $batch->fresh();
    }

    /**
     * @throws \Exception
     */
    private function queueSingleCsvJobs(EligibilityBatch $batch): EligibilityBatch
    {
        $result = null;

        if (array_key_exists('patientList', $batch->options)) {
            $result = $this->processEligibilityService->processCsvForEligibility($batch);
        } elseif (array_keys_exist(['folder', 'fileName'], $batch->options)) {
            $result = $this->processEligibilityService->processGoogleDriveCsvForEligibility($batch);
        }

        if ($result) {
            $batch->status = EligibilityBatch::STATUSES['processing'];
            $batch->save();

            return $batch;
        }

        $unprocessedQuery = EligibilityJob::whereBatchId($batch->id)
            ->where('status', '<', 2);

        $unprocessedQuery->take(200)->get()->each(function ($job) use ($batch) {
            ProcessSinglePatientEligibility::dispatchNow(
                $job,
                $batch,
                $batch->practice
            );
        });

        if ( ! $unprocessedQuery->exists()) {
            $batch->status = EligibilityBatch::STATUSES['complete'];
            $batch->save();

            return $batch;
        }

        $batch->status = EligibilityBatch::STATUSES['processing'];
        $batch->save();

        return $batch;
    }

    /**
     * Read the file containing patient data for batch type `clh_medical_record_template`, using a fopen.
     *
     * @param $pathToFile
     * @param $batch
     */
    private function readUsingFopen($pathToFile, $batch)
    {
        $handle = @fopen($pathToFile, 'r');
        if ($handle) {
            while ( ! feof($handle)) {
                if (false !== ($buffer = fgets($handle))) {
                    $mr = new JsonMedicalRecordAdapter($buffer);
                    $mr->createEligibilityJob($batch);
                }
            }
            fclose($handle);
        }
    }

    /**
     * Read the file containing patient data for batch type `clh_medical_record_template`, using a generator.
     */
    private function readUsingGenerator(string $pathToFile, EligibilityBatch $batch)
    {
        $iterator = read_file_using_generator($pathToFile);

        foreach ($iterator as $iteration) {
            if ( ! $iteration) {
                continue;
            }

            $mr = new JsonMedicalRecordAdapter($iteration);
            $mr->createEligibilityJob($batch);
        }
    }
}

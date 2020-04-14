<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Core\GoogleDrive;
use CircleLinkHealth\Eligibility\Adapters\JsonMedicalRecordAdapter;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use CircleLinkHealth\Eligibility\ProcessEligibilityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessEligibilityBatch implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * @var EligibilityBatch
     */
    protected $batch;
    
    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 900;

    /**
     * @var \CircleLinkHealth\Eligibility\ProcessEligibilityService
     */
    private $processEligibilityService;

    /**
     * Create a new job instance.
     */
    public function __construct(EligibilityBatch $batch)
    {
        $this->batch = $batch;
    }
    
    /**
     * Execute the job.
     *
     * @param ProcessEligibilityService $processEligibilityService
     *
     * @return void
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function handle(ProcessEligibilityService $processEligibilityService)
    {
        ini_set('upload_max_filesize', '200M');
        ini_set('post_max_size', '200M');
        ini_set('max_input_time', 900);
        ini_set('max_execution_time', 900);
        
        $this->processEligibilityService = $processEligibilityService;

        switch ($this->batch->type) {
            case EligibilityBatch::TYPE_ONE_CSV:
                $this->batch = $this->queueSingleCsvJobs($this->batch);
                break;
            case EligibilityBatch::TYPE_GOOGLE_DRIVE_CCDS:
                $this->batch = $this->queueGoogleDriveJobs($this->batch);
                break;
            case EligibilityBatch::CLH_MEDICAL_RECORD_TEMPLATE:
                $this->batch = $this->queueClhMedicalRecordTemplateJobs($this->batch);
                break;
            case EligibilityBatch::ATHENA_API:
                $this->batch = $this->queueAthenaJobs($this->batch);
                break;
        }

        $this->afterProcessingHook($this->batch);
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
                ->notify($batch);
        }
    }
    
    /**
     * @param EligibilityBatch $batch
     *
     * @return EligibilityBatch
     * @throws \Exception
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

    private function queueAthenaJobs(EligibilityBatch $batch): EligibilityBatch
    {
        ini_set('memory_limit', '200M');
        
        $query          = TargetPatient::whereBatchId($batch->id)->whereStatus(TargetPatient::STATUS_TO_PROCESS);
        $targetPatients = $query->with('batch')->chunkById(30, function ($targetPatients) use ($batch) {
            $batch->status = EligibilityBatch::STATUSES['processing'];
            $batch->save();

            $targetPatients->each(function (TargetPatient $targetPatient) use ($batch) {
                ProcessTargetPatientForEligibility::dispatch($targetPatient);
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
        if ( ! (bool) $batch->options['finishedReadingFile'] ?? false) {
            ini_set('memory_limit', '1000M');

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
    
    private function queueSingleCsvJobs(EligibilityBatch $batch): EligibilityBatch
    {
        if (array_keys_exist(['folder', 'fileName'], $batch->options) && !! $batch->options['finishedReadingFile'] !== true) {
            $result = $this->processEligibilityService->processGoogleDriveCsvForEligibility($batch);
            
            if ($result) {
                $batch->status = EligibilityBatch::STATUSES['processing'];
                $batch->save();
        
                return $batch;
            }
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
    
            CreateEligibilityJobFromJsonMedicalRecord::dispatch($batch, $iteration)->onQueue('low');
        }
    }
}

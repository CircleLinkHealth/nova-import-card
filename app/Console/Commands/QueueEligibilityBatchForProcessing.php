<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\EligibilityBatch;
use App\EligibilityJob;
use App\Jobs\CheckCcdaEnrollmentEligibility;
use App\Jobs\MakePhoenixHeartWelcomeCallList;
use App\Jobs\ProcessCcda;
use App\Jobs\ProcessSinglePatientEligibility;
use App\Models\MedicalRecords\Ccda;
use App\Models\PatientData\PhoenixHeart\PhoenixHeartName;
use App\Services\AthenaAPI\DetermineEnrollmentEligibility as AthenaDetermineEnrollmentEligibility;
use App\Services\CCD\ProcessEligibilityService;
use App\Services\Eligibility\Adapters\JsonMedicalRecordAdapter;
use App\Services\GoogleDrive;
use App\TargetPatient;
use CircleLinkHealth\Customer\Entities\Practice;
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
     *
     * @param ProcessEligibilityService $processEligibilityService
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
        if ($batch->isCompleted()) {
            $this->processEligibilityService
                ->notifySlack($batch);
        }
    }

    /**
     * @param EligibilityBatch $batch
     *
     * @throws \League\Flysystem\FileNotFoundException
     *
     * @return EligibilityBatch
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
            $batch->status = 2;
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

    /**
     * @return EligibilityBatch|null
     */
    private function getBatch(): ?EligibilityBatch
    {
        return EligibilityBatch::where('status', '<', 2)
            ->with('practice')
            ->first();
    }

    private function queueAthenaJobs(EligibilityBatch $batch): EligibilityBatch
    {
        $athenaService  = app(AthenaDetermineEnrollmentEligibility::class);
        $query          = TargetPatient::whereBatchId($batch->id)->whereStatus(TargetPatient::STATUS_TO_PROCESS);
        $targetPatients = $query->with('batch')->chunkById(100, function ($targetPatients) use (&$athenaService) {
            foreach ($targetPatients as $patient) {
                $athenaService->determineEnrollmentEligibility($patient);
            }
        });

        // Mark batch as processed by default
        $batch->status = 3;

        if ($query->exists()) {
            // Mark batch as processing if there are patients to precess in DB
            $batch->status = 1;
        }

        $batch->save();

        return $batch;
    }

    /**
     * @param EligibilityBatch $batch
     *
     * @throws \League\Flysystem\FileNotFoundException
     *
     * @return EligibilityBatch
     */
    private function queueClhMedicalRecordTemplateJobs(EligibilityBatch $batch): EligibilityBatch
    {
        if ( ! (bool) $batch->options['finishedReadingFile']) {
            ini_set('memory_limit', '800M');

            $created = $this->createEligibilityJobsFromJsonFile($batch);
        }

        EligibilityJob::whereBatchId($batch->id)
            ->where('status', '=', 0)
            ->inRandomOrder()
            ->take(300)
            ->get()
            ->each(function ($job) use ($batch) {
                ProcessSinglePatientEligibility::dispatch(
                    collect([$job->data]),
                    $job,
                    $batch,
                    $batch->practice
                          )->onQueue('low');
            });

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

    /**
     * @param EligibilityBatch $batch
     *
     * @return EligibilityBatch
     */
    private function queueGoogleDriveJobs(EligibilityBatch $batch): EligibilityBatch
    {
        $result = $this->processEligibilityService->fromGoogleDrive($batch);

        if ($result) {
            $batch->status = EligibilityBatch::STATUSES['processing'];
            $batch->save();

            return $batch;
        }

        $practice = Practice::findOrFail($batch->practice_id);

        $unprocessed = Ccda::whereBatchId($batch->id)
            ->whereStatus(Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY)
            ->inRandomOrder()
            ->take(10)
            ->get()
            ->map(function ($ccda) use ($batch, $practice) {
                ProcessCcda::withChain([
                    (new CheckCcdaEnrollmentEligibility(
                        $ccda->id,
                        $practice,
                        $batch
                    ))->onQueue('low'),
                ])->dispatch($ccda->id)
                    ->onQueue('low');

                return $ccda;
            });

        if ($unprocessed->isEmpty()) {
            $batch->status = EligibilityBatch::STATUSES['complete'];
            $batch->save();

            return $batch;
        }

        $batch->status = EligibilityBatch::STATUSES['processing'];
        $batch->save();

        return $batch;
    }

    /**
     * @param $batch
     *
     * @return EligibilityBatch
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
     * @param EligibilityBatch $batch
     *
     * @throws \Exception
     *
     * @return EligibilityBatch
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
                collect([$job->data]),
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
     *
     * @param string           $pathToFile
     * @param EligibilityBatch $batch
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

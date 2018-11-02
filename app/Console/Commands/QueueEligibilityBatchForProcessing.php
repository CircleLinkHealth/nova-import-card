<?php

namespace App\Console\Commands;

use App\EligibilityBatch;
use App\EligibilityJob;
use App\Jobs\CheckCcdaEnrollmentEligibility;
use App\Jobs\MakePhoenixHeartWelcomeCallList;
use App\Jobs\ProcessCcda;
use App\Jobs\ProcessSinglePatientEligibility;
use App\Models\MedicalRecords\Ccda;
use App\Models\PatientData\PhoenixHeart\PhoenixHeartName;
use App\Practice;
use App\Services\CCD\ProcessEligibilityService;
use App\Services\Eligibility\Adapters\JsonMedicalRecordAdapter;
use App\Services\GoogleDrive;
use App\TargetPatient;
use Illuminate\Console\Command;
use Storage;

class QueueEligibilityBatchForProcessing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:process';

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
     * @return EligibilityBatch|null
     */
    private function getBatch(): ?EligibilityBatch
    {
        return EligibilityBatch::where('status', '<', 2)
                               ->with('practice')
                               ->first();
    }

    /**
     * @param EligibilityBatch $batch
     *
     * @return EligibilityBatch
     * @throws \Exception
     */
    private function queueSingleCsvJobs(EligibilityBatch $batch): EligibilityBatch
    {
        $result = $this->processEligibilityService->processCsvForEligibility($batch);

        if ($result) {
            $batch->status = EligibilityBatch::STATUSES['processing'];
            $batch->save();

            return $batch;
        }

        $unprocessed = EligibilityJob::whereBatchId($batch->id)
                                     ->where('status', '<', 2)
                                     ->take(500)
                                     ->get();

        if ($unprocessed->isEmpty()) {
            $batch->status = EligibilityBatch::STATUSES['complete'];
            $batch->save();

            return $batch;
        }

        $unprocessed->map(function ($job) use ($batch) {
            (new ProcessSinglePatientEligibility(
                collect([$job->data]),
                $job,
                $batch,
                $batch->practice
            ))->handle();
        });

        $batch->status = EligibilityBatch::STATUSES['processing'];
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
                                   (new CheckCcdaEnrollmentEligibility($ccda->id,
                                       $practice,
                                       $batch))->onQueue('low'),
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
        MakePhoenixHeartWelcomeCallList::dispatch($batch)->onQueue('low');

        $jobsToBeProcessedExist = EligibilityJob::whereBatchId($batch->id)
                                                ->where('status', '<', 2)
                                                ->exists();

        if ($jobsToBeProcessedExist) {
            $batch->status = EligibilityBatch::STATUSES['processing'];
        } elseif (! PhoenixHeartName::where('processed', '=', false)->exists()) {
            $this->status = EligibilityBatch::STATUSES['complete'];
        }

        $batch->save();

        return $batch->fresh();
    }

    /**
     * @param EligibilityBatch $batch
     *
     * @return EligibilityBatch
     * @throws \League\Flysystem\FileNotFoundException
     */
    private function queueClhMedicalRecordTemplateJobs(EligibilityBatch $batch): EligibilityBatch
    {
        if ( ! ! ! $batch->options['finishedReadingFile']) {
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

        if ($jobsToBeProcessedCount == 0) {
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
     * @throws \League\Flysystem\FileNotFoundException
     */
    private function createEligibilityJobsFromJsonFile(EligibilityBatch $batch): EligibilityBatch
    {
        $driveFolder   = $batch->options['folder'];
        $driveFileName = $batch->options['fileName'];

        $driveHandler = new GoogleDrive();
        $stream       = $driveHandler
            ->getFileStream($driveFileName, $driveFolder);

        $localDisk = Storage::disk('local');

        $fileName   = "eligibl_{$driveFileName}";
        $pathToFile = storage_path("app/$fileName");

        $savedLocally = $localDisk->put($fileName, $stream);

        if ( ! $savedLocally) {
            throw new \Exception("Failed saving $pathToFile");
        }

        try {
            \Log::debug("BEGIN creating eligibility jobs from json file in google drive: [`folder => $driveFolder`, `filename => $driveFileName`]");

            //Reading the file using a generator is expected to consume less memory.
            //implemented both to experiment
//            $this->readWithoutUsingGenerator($pathToFile, $batch);
            $this->readUsingGenerator($pathToFile, $batch);

            \Log::debug("FINISH creating eligibility jobs from json file in google drive: [`folder => $driveFolder`, `filename => $driveFileName`]");

            $mem = format_bytes(memory_get_peak_usage());

            \Log::debug("BEGIN deleting `$fileName`");
            $deleted = $localDisk->delete($fileName);
            \Log::debug("FINISH deleting `$fileName`");


            \Log::debug("memory_get_peak_usage: $mem");

            $options                        = $batch->options;
            $options['finishedReadingFile'] = true;
            $batch->options                 = $options;
            $batch->save();

            return $batch;
        } catch (\Exception $e) {
            \Log::debug("EXCEPTION `{$e->getMessage()}`");

            \Log::debug("BEGIN deleting `$fileName`");
            $deleted = $localDisk->delete($fileName);
            \Log::debug("FINISH deleting `$fileName`");

            throw $e;
        }
    }

    /**
     * Read the file containing patient data for batch type `clh_medical_record_template`, using a fopen
     *
     * @param $pathToFile
     * @param $batch
     */
    private function readUsingFopen($pathToFile, $batch)
    {
        $handle = @fopen($pathToFile, "r");
        if ($handle) {
            while ( ! feof($handle)) {
                if (($buffer = fgets($handle)) !== false) {
                    $mr = new JsonMedicalRecordAdapter($buffer);
                    $mr->createEligibilityJob($batch);
                }
            }
            fclose($handle);
        }
    }

    /**
     * Read the file containing patient data for batch type `clh_medical_record_template`, using a generator
     *
     * @param string $pathToFile
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

    /**
     * Run this tasks after processing a batch
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

    private function queueAthenaJobs(EligibilityBatch $batch): EligibilityBatch
    {
        //If the Athena batch has not patients, mark it as complete
        if ( ! TargetPatient::whereBatchId($batch->id)->exists()) {
            $batch->status = 3;
            $batch->save();
        }

        return $batch;
    }
}

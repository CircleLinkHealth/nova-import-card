<?php

namespace App\Console\Commands;

use App\EligibilityBatch;
use App\EligibilityJob;
use App\Jobs\CheckCcdaEnrollmentEligibility;
use App\Jobs\MakePhoenixHeartWelcomeCallList;
use App\Jobs\ProcessCcda;
use App\Jobs\ProcessSinglePatientEligibility;
use App\Models\MedicalRecords\Ccda;
use App\Practice;
use App\Services\CCD\ProcessEligibilityService;
use App\Services\Eligibility\Adapters\JsonMedicalRecordAdapter;
use App\Services\GoogleDrive;
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
        $batch = EligibilityBatch::where('status', '<', 2)
                                 ->whereType(EligibilityBatch::TYPE_ONE_CSV)
                                 ->with('practice')
                                 ->first();

        if ($batch) {
            $this->queueSingleCsvJobs($batch);

            return true;
        }

        $batch = EligibilityBatch::where('status', '<', 2)
                                 ->whereType(EligibilityBatch::TYPE_GOOGLE_DRIVE_CCDS)
                                 ->first();

        if ($batch) {
            $this->queueGoogleDriveJobs($batch);

            return true;
        }

        $batch = EligibilityBatch::where('status', '<', 2)
                                 ->whereType(EligibilityBatch::TYPE_PHX_DB_TABLES)
                                 ->first();

        if ($batch) {
            $this->queuePHXJobs($batch);

            return true;
        }

        $batch = EligibilityBatch::where('status', '<', 2)
                                 ->whereType(EligibilityBatch::CLH_MEDICAL_RECORD_TEMPLATE)
                                 ->first();

        if ($batch) {
            $this->queueClhMedicalRecordTemplateJobs($batch);

            return true;
        }
    }

    private function queueSingleCsvJobs(EligibilityBatch $batch)
    {
        $result = $this->processEligibilityService->processCsvForEligibility($batch);

        if ($result) {
            $batch->status = EligibilityBatch::STATUSES['processing'];
            $batch->save();

            return $batch;
        }

        $unprocessed = EligibilityJob::whereBatchId($batch->id)
                                     ->where('status', '<', 2)
                                     ->take(10)
                                     ->get();

        if ($unprocessed->isEmpty()) {
            $batch->status = EligibilityBatch::STATUSES['complete'];
            $batch->save();

            return $batch;
        }

        $unprocessed->map(function ($job) use ($batch) {
            ProcessSinglePatientEligibility::dispatch(
                collect([$job->data]),
                $job,
                $batch,
                $batch->practice
            );
        });

        $batch->status = EligibilityBatch::STATUSES['processing'];
        $batch->save();

        return $batch;
    }

    private function queueGoogleDriveJobs(EligibilityBatch $batch)
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
                                       $batch))->onQueue('ccda-processor'),
                               ])->dispatch($ccda->id)
                                          ->onQueue('ccda-processor');

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

    private function queuePHXJobs($batch)
    {
        MakePhoenixHeartWelcomeCallList::dispatch($batch)->onQueue('ccda-processor');
    }

    private function queueClhMedicalRecordTemplateJobs(EligibilityBatch $batch)
    {
        if ( ! ! ! $batch->options['finishedReadingFile']) {
            $created = $this->createEligibilityJobsFromJsonFile($batch);
        }

        $unprocessed = EligibilityJob::whereBatchId($batch->id)
                                     ->where('status', '<', 2)
                                     ->take(10)
                                     ->get();

        if ($unprocessed->isNotEmpty()) {
            $batch->status = EligibilityBatch::STATUSES['processing'];
        } else {
            $batch->status = EligibilityBatch::STATUSES['complete'];
        }

        $unprocessed->each(function ($job) use ($batch) {
            ProcessSinglePatientEligibility::dispatch(
                collect([$job->data]),
                $job,
                $batch,
                $batch->practice
            );
        });

        $batch->save();

        return $batch;
    }

    /**
     * @param EligibilityBatch $batch
     *
     * @return bool
     * @throws \League\Flysystem\FileNotFoundException
     */
    private function createEligibilityJobsFromJsonFile(EligibilityBatch $batch)
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

            return true;
        } catch (\Exception $e) {
            \Log::debug("EXCEPTION `{$e->getMessage()}`");

            \Log::debug("BEGIN deleting `$fileName`");
            $deleted = $localDisk->delete($fileName);
            \Log::debug("FINISH deleting `$fileName`");

            throw $e;
        }
    }

    private function readWithoutUsingGenerator($pathToFile, $batch)
    {
        $handle = @fopen($pathToFile, "r");
        if ($handle) {
            while ( ! feof($handle)) {
                if (($buffer = fgets($handle)) !== false) {
                    $mr = new JsonMedicalRecordAdapter($buffer);
                    $mr->firstOrUpdateOrCreateEligibilityJob($batch);
                }
            }
            fclose($handle);
        }
    }

    private function readUsingGenerator(string $pathToFile, EligibilityBatch $batch)
    {
        $iterator = read_file_using_generator($pathToFile);

        foreach ($iterator as $iteration) {
            if ( ! $iteration) {
                continue;
            }

            $mr = new JsonMedicalRecordAdapter($iteration);
            $mr->firstOrUpdateOrCreateEligibilityJob($batch);
        }
    }
}

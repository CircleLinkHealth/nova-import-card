<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Core\GoogleDrive;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Eligibility\Adapters\JsonMedicalRecordAdapter;
use CircleLinkHealth\SharedModels\Entities\EligibilityBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CreateEligibilityJobsFromCLHMedicalRecordJson implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    
    protected int $batchId;
    
    /**
     * Create a new job instance.
     *
     * @param int $batchId
     */
    public function __construct(
        int $batchId
    ) {
        $this->batchId = $batchId;
    }

    /**
     * Execute the job.
     *
     * @throws \Exception
     */
    public function handle()
    {
        $batch = EligibilityBatch::findOrFail($this->batchId);
        
        if ( ! isset($batch->options['folder'])) {
            \Log::critical("Batch with id:{$batch->id} does not have a folder path.");
        
            return null;
        }
    
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
        
            return $batch;
        }
    
        $localDisk = Storage::disk('local');
    
        $fileName   = "eligibl_{$driveFileName}";
        $pathToFile = storage_path("app/${fileName}");
    
        $savedLocally = $localDisk->put($fileName, $stream);
    
        if ( ! $savedLocally) {
            throw new \Exception("Failed saving ${pathToFile}");
        }
    
        try {
            \Log::debug(
                "BEGIN creating eligibility jobs from json file in google drive: [`folder => ${driveFolder}`, `filename => ${driveFileName}`]"
            );
            
            $this->readUsingGenerator($pathToFile, $batch);
        
            \Log::debug(
                "FINISH creating eligibility jobs from json file in google drive: [`folder => ${driveFolder}`, `filename => ${driveFileName}`]"
            );
        
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
        } catch (\Exception $e) {
            \Log::debug("EXCEPTION `{$e->getMessage()}`");
        
            \Log::debug("BEGIN deleting `${fileName}`");
            $deleted = $localDisk->delete($fileName);
            \Log::debug("FINISH deleting `${fileName}`");
        
            throw $e;
        }
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
            
            CreateEligibilityJobFromJsonMedicalRecord::dispatch($batch, $iteration)->onQueue(getCpmQueueName(CpmConstants::LOW_QUEUE));
        }
    }
}

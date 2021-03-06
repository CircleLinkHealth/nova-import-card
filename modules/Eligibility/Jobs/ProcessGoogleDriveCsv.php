<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Core\GoogleDrive;
use CircleLinkHealth\Eligibility\Adapters\MultipleFiledsTemplateToString;
use CircleLinkHealth\Eligibility\DTO\CsvPatientList;
use CircleLinkHealth\Eligibility\Exceptions\CsvEligibilityListStructureValidationException;
use CircleLinkHealth\Eligibility\ValidatesEligibility;
use CircleLinkHealth\SharedModels\Entities\EligibilityBatch;
use CircleLinkHealth\SharedModels\Entities\EligibilityJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessGoogleDriveCsv implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use ValidatesEligibility;

    protected int $batchId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $batchId)
    {
        $this->batchId = $batchId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $batch = EligibilityBatch::find($this->batchId);

        if ( ! $batch) {
            return;
        }
    
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
            
                $patient = sanitize_array_keys(MultipleFiledsTemplateToString::fromRow($row));
            
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
}

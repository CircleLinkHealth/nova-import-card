<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Eligibility\DTO\PracticePullFileInGoogleDrive;
use CircleLinkHealth\SharedModels\Entities\EligibilityBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ImportPracticePullCsvsFromGoogleDrive implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int    $batchId;
    protected PracticePullFileInGoogleDrive $file;

    /**
     * Create a new job instance.
     */
    public function __construct(int $batchId, PracticePullFileInGoogleDrive $file)
    {
        $this->batchId = $batchId;
        $this->file    = $file;
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
        $practiceId = $batch->practice_id;
        $cloudDisk  = Storage::disk('google');
        $path        = $this->file->getPath();

        if ($batch->isFinishedFetchingPracticePullCsvs()) {
            return null;
        }
    
        $readStream = $cloudDisk->getDriver()->readStream($path);
        $targetFile = storage_path(now()->timestamp);
        file_put_contents($targetFile, stream_get_contents($readStream), FILE_APPEND);
        $count = 0;
        
        $importerClass = $this->file->getImporter();
        $importer = new $importerClass($practiceId);
    
        foreach (parseCsvToArray($targetFile) as $row) {
            $model = $importer->model($row);
            $stored = app(get_class($model))->updateOrCreate($model->toArray());
            ++$count;
        }
        
        $this->file->setFinishedProcessingAt(now());
        
        $batch->addPracticePullGoogleDriveFile($this->file);
        $batch->save();
    }
}

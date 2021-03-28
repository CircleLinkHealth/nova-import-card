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
use Maatwebsite\Excel\Facades\Excel;

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
        
        $media = $this->storeMedia($batch, $this->file);

        $importerClass = $this->file->getImporter();
        $importer      = new $importerClass($practiceId);

        try {
            Excel::import($importer, $media->getPath(), 'media');
        } catch (\Exception $e) {
            \Log::error("EligibilityBatchException[{$this->batchId}] at {$e->getFile()}:{$e->getLine()} {$e->getMessage()} || {$e->getTraceAsString()}");
        }

        $this->file->setFinishedProcessingAt(now());

        $batch->addPracticePullGoogleDriveFile($this->file);
        $batch->save();
    }

    private function storeMedia(EligibilityBatch $batch, PracticePullFileInGoogleDrive $file): \Spatie\MediaLibrary\Models\Media
    {
        $cloudDisk  = Storage::disk('google');
        $readStream = $cloudDisk->getDriver()->readStream($file->getPath());
        $targetFile = storage_path(strtolower(now()->timestamp."-batch-{$batch->id}-pull-{$file->getTypeOfData()}-data-{$file->getName()}"));
        file_put_contents($targetFile, stream_get_contents($readStream), FILE_APPEND);

        return $batch->addMedia($targetFile)
            ->toMediaCollection($file->getTypeOfData());
    }
}

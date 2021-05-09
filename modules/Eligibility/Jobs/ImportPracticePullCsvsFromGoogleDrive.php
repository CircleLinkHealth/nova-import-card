<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Customer\Entities\Media;
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

        $media = $this->firstOrCreateMedia($batch, $this->file);

        $importerClass = $this->file->getImporter();
        $importer      = new $importerClass($batch->practice_id);
        $path          = $media->getPath();

        try {
            $this->startLog($media, $this->file)->save();
            Excel::import($importer, $path, 'media');
            $this->file->setFinishedProcessingAt(now());
            $this->endLog($media, $this->file)->save();
        } catch (\Exception $e) {
            \Log::error("EligibilityBatchException[{$this->batchId}] at {$e->getFile()}:{$e->getLine()} {$e->getMessage()} || {$e->getTraceAsString()}");
        }
    }

    private function endLog(Media $media, PracticePullFileInGoogleDrive $file): Media
    {
        return $media->setCustomProperty('finishedProcessingAt', $file->getFinishedProcessingAt());
    }

    private function firstOrCreateMedia(EligibilityBatch $batch, PracticePullFileInGoogleDrive $file): Media
    {
        $existing = $batch->media()->where('name', $file->getFileNameWithoutExtension())->first();

        if ($existing) {
            return $existing;
        }

        $cloudDisk  = Storage::disk('google');
        $readStream = $cloudDisk->getDriver()->readStream($file->getPath());
        $targetFile = storage_path($file->getName());
        file_put_contents($targetFile, stream_get_contents($readStream), FILE_APPEND);

        return $batch->addMedia($targetFile)
            ->toMediaCollection($file->getTypeOfData());
    }

    private function startLog(Media $media, PracticePullFileInGoogleDrive $file): Media
    {
        $media->setCustomProperty('dispatchedAt', $file->getDispatchedAt());
        $media->setCustomProperty('importer', $file->getImporter());
        $media->setCustomProperty('name', $file->getName());
        $media->setCustomProperty('path', $file->getPath());

        return $media->setCustomProperty('typeOfData', $file->getTypeOfData());
    }
}

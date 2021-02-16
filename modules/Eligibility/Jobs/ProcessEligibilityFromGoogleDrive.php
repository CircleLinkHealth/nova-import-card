<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use CircleLinkHealth\SharedModels\Entities\EligibilityBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessEligibilityFromGoogleDrive implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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
        $cloudDisk = Storage::disk('google');
        $recursive = false; // Get subdirectories also?
        $dir       = $batch->options['dir'];

        if ($batch->isFinishedFetchingFiles()) {
            return null;
        }

        $collection = collect($cloudDisk->listContents($dir, $recursive));

        $options                  = $batch->options;
        $options['numberOfFiles'] = $collection->count();
        $batch->options           = $options;
        $batch->save();

        echo "\n batch {$batch->id}: {$options['numberOfFiles']} total files on drive";

        $alreadyProcessed = Media::select('file_name')->whereModelType(Ccda::class)->whereIn(
            'model_id',
            function ($query) use ($batch) {
                $query->select('id')
                    ->from((new Ccda())->getTable())
                    ->where('batch_id', $batch->id);
            }
        )->distinct()->pluck('file_name');

        echo "\n batch {$batch->id}: {$alreadyProcessed->count()} CCDs already processed from this batch.";

        $col = $collection
            ->where('type', '=', 'file')
            ->whereIn(
                'mimetype',
                [
                    'text/xml',
                    'application/xml',
                ]
            )
            ->whereNotIn('name', $alreadyProcessed->all());

        echo "\n batch {$batch->id}: {$col->count()} CCDs to fetch from drive";

        if ($col->isEmpty()) {
            return false;
        }
        $col->whenNotEmpty(
            function ($collection) use ($batch) {
                $i = 0;
                $collection->each(
                    function ($file) use (
                        $batch,
                        &$i
                    ) {
                        ProcessCcdaFromGoogleDrive::dispatch($file, $batch);

                        ++$i;
                        echo "\n batch {$batch->id}: processing file $i";
                    }
                );
            }
        );

        return true;
    }
}

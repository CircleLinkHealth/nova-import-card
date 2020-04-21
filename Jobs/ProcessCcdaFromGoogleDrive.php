<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessCcdaFromGoogleDrive implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * @var \CircleLinkHealth\Eligibility\Entities\EligibilityBatch
     */
    protected $batch;

    /**
     * @var array
     */
    protected $googleDriveFile;

    /**
     * Create a new job instance.
     */
    public function __construct(array $googleDriveFile, EligibilityBatch $batch)
    {
        $this->googleDriveFile = $googleDriveFile;
        $this->batch           = $batch;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $cloudDisk = Storage::disk('google');

        $driveFilePath = $this->googleDriveFile['path'];

        $fileExists = Media::whereModelType(Ccda::class)->whereIn('model_id', function ($query) {
            $query->select('id')
                ->from((new Ccda())->getTable())
                ->where('batch_id', $this->batch->id);
        })->where('file_name', $this->googleDriveFile['name'])->exists();

        if ($fileExists) {
            return;
        }

        try {
            $rawData = $cloudDisk->get($driveFilePath);
        } catch (FileNotFoundException $e) {
            // Jobs Fail due to file not found, but upon retrying, it works.
            // If a file was not found, just return and thefile will be retried when rescheduled.
            return;
        }

        $ccda = Ccda::create([
            'batch_id'    => $this->batch->id,
            'source'      => Ccda::GOOGLE_DRIVE."_{$this->googleDriveFile['dirname']}",
            'xml'         => $rawData,
            'status'      => Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY,
            'imported'    => false,
            'practice_id' => $this->batch->practice_id,
            'filename'    => $this->googleDriveFile['name'],
        ]);

        if ( ! $ccda->practice_id) {
            //for some reason it doesn't save practice_id when using Ccda::create([])
            $ccda->practice_id = $this->batch->practice_id;
            $ccda->save();
        }

        $this->batch->loadMissing('practice');

        ProcessCcda::withChain([
            (new CheckCcdaEnrollmentEligibility($ccda->id, $this->batch->practice, $this->batch))->onQueue('low'),
        ])->dispatch($ccda->id)
            ->onQueue('low');
    }
}

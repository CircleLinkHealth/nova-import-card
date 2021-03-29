<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use App\Nova\Importers\PracticePull\Allergies;
use App\Nova\Importers\PracticePull\Demographics;
use App\Nova\Importers\PracticePull\Medications;
use App\Nova\Importers\PracticePull\Problems;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Eligibility\DTO\PracticePullFileInGoogleDrive;
use CircleLinkHealth\SharedModels\Entities\EligibilityBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

class DispatchGoogleDrivePracticePullCsvsReadJobsAndEligibilityProcessing implements ShouldQueue, ShouldBeEncrypted
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
        $batchId = $this->batchId;
        $batch = EligibilityBatch::with('media')->find($batchId);

        if ( ! $batch) {
            return;
        }
        $cloudDisk = Storage::disk('google');
        $recursive = false;
        $dir       = $batch->options['folder'];

        if ($batch->isFinishedFetchingPracticePullCsvs()) {
            \Log::debug("EligibilityBatch[{$batchId}] isFinishedFetchingPracticePullCsvs");

            return null;
        }

        $jobs = collect($cloudDisk->listContents($dir, $recursive))
            ->where('type', '=', 'dir')
            ->whereIn('name', [
                'Allergies',
                'Demographics',
                'Medications',
                'Problems',
            ])->map(function ($driveFolder) use ($cloudDisk, $batch, $batchId) {
                return collect($cloudDisk->listContents($driveFolder['path'], false))
                    ->where('type', '=', 'file')
                    ->whereIn('extension', [
                        'xls',
                        'xlsx',
                        'csv',
                    ])->map(function ($driveFile) use (&$filesToImport, $driveFolder, $batch, $batchId) {
                        if ($batch->media->where('file_name', str_replace(' ', '-', $driveFile['name']))->isNotEmpty()) {
                            \Log::debug("EligibilityBatch[{$batchId}] read job[{$driveFolder['name']}][{$driveFile['name']}] already exists");

                            return false;
                        }

                        \Log::debug("EligibilityBatch[{$batchId}] create read job[{$driveFolder['name']}][{$driveFile['name']}]");

                        return new ImportPracticePullCsvsFromGoogleDrive($batchId, new PracticePullFileInGoogleDrive($driveFile['name'], $driveFile['path'], $driveFolder['name'], $this->importers($driveFolder['name'])));
                    })->filter();
            });
        
        $jobs->push([
            function () use ($batchId) {
                return (new DispatchPracticePullEligibilityBatch($batchId))->splitToBatches();
            },
        ]);

        Bus::chain($jobs->flatten()->all())
            ->onQueue(getCpmQueueName(CpmConstants::LOW_QUEUE))
            ->dispatch();
    }

    private function importers(string $for)
    {
        return [
            'Allergies'    => Allergies::class,
            'Demographics' => Demographics::class,
            'Medications'  => Medications::class,
            'Problems'     => Problems::class,
        ][$for];
    }
}

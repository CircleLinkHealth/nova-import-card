<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Models\PatientData\LGH\LGHInsurance;
use App\ProcessedFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportLGHInsurance implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var string
     */
    protected $fileName;

    /**
     * Create a new job instance.
     *
     * @param $fileName
     */
    public function __construct($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        if (false === stripos($this->fileName, 'circlelink_supplement_')) {
            return;
        }

        $path = config('filesystems.disks.ccdas.root').'/'.$this->fileName;

        $exists = ProcessedFile::wherePath($path)->first();

        if ($exists) {
            \Log::info("Already processed ${path}");

            return;
        }

        \Log::info("Started Importing LGH Insurance from: {$this->fileName}");

        $csv = parseCsvToArray($path);

        if ( ! $csv) {
            return;
        }

        foreach ($csv as $row) {
            LGHInsurance::updateOrCreate([
                'mrn' => $row['mrn'],
            ], $row);
        }

        ProcessedFile::create([
            'path' => $path,
        ]);
    }
}

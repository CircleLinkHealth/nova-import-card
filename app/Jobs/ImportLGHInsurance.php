<?php

namespace App\Jobs;

use App\Models\PatientData\LGH\LGHInsurance;
use App\ProcessedFiles;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportLGHInsurance implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     *
     *
     * @var string $fileName
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
     *
     * @return void
     */
    public function handle()
    {
        if (stripos($this->fileName, 'circlelink_supplement_') == false) {
            return;
        }

        $path = config('filesystems.disks.ccdas.root') . '/' . $this->fileName;

        $exists = ProcessedFiles::wherePath($path)->first();

        if ($exists) {
            \Log::info("Already processed $path");

            return;
        }

        \Log::info("Started Importing LGH Insurance from: $this->fileName");

        $csv = parseCsvToArray($path);

        if (!$csv) {
            return;
        }

        foreach ($csv as $row) {
            LGHInsurance::updateOrCreate([
                'mrn' => $row['mrn']
            ], $row);
        }

        ProcessedFiles::create([
            'path' => $path,
        ]);
    }
}

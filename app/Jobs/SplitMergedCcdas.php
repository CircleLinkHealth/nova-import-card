<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Models\MedicalRecords\Ccda;
use App\ProcessedFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SplitMergedCcdas implements ShouldQueue
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
     * @param mixed $fileName
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
        if (false == stripos($this->fileName, '.xml')) {
            return;
        }

        $path = config('filesystems.disks.ccdas.root').'/'.$this->fileName;

        $exists = ProcessedFile::wherePath($path)->first();

        if ($exists) {
            \Log::info("Already processed ${path}");

            return;
        }

        \Log::info("Started Splitting {$this->fileName}");

        $exploded = explode('</ClinicalDocument>', \Storage::disk('ccdas')->get($this->fileName));

        $count = 0;

        foreach ($exploded as $ccdaString) {
            if (false !== stripos($ccdaString, '<ClinicalDocument')) {
                $ccda = Ccda::create([
                    'source'   => Ccda::SFTP_DROPBOX,
                    'imported' => false,
                    'xml'      => trim($ccdaString.'</ClinicalDocument>'),
                    'status'   => Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY,
                ]);

                ++$count;
            }
        }

        ProcessedFile::create([
            'path' => $path,
        ]);

        \Log::info("Finished Splitting {$this->fileName}! ${count} CCDAs found.");
    }
}

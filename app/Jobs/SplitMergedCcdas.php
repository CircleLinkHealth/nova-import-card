<?php

namespace App\Jobs;

use App\CLH\Repositories\CCDImporterRepository;
use App\Importer\Loggers\Ccda\CcdToLogTranformer;
use App\Models\MedicalRecords\Ccda;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SplitMergedCcdas implements ShouldQueue
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
     * @return void
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
        if (stripos($this->fileName, '.xml') == false) {
            return;
        }

        \Log::info("Started Splitting $this->fileName");

        $exploded = explode('</ClinicalDocument>', \Storage::disk('ccdas')->get($this->fileName));

        $count = 0;

        foreach ($exploded as $ccdaString) {
            if (stripos($ccdaString, '<ClinicalDocument') !== false) {
                $ccda = Ccda::create([
                    'source'   => Ccda::SFTP_DROPBOX,
                    'imported' => false,
                    'xml'      => trim($ccdaString . '</ClinicalDocument>'),
                    'status'   => Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY,
                ]);

                $job = (new ProcessCcda($ccda))->delay(Carbon::now()->addMinutes(10));

                dispatch($job);

                $count++;
            }
        }

        \Log::info("Finished Splitting $this->fileName! $count CCDAs found.");
    }
}

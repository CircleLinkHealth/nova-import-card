<?php

namespace App\Jobs;

use App\Models\MedicalRecords\Ccda;
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
                $ccdas[] = Ccda::create([
                    'source'   => Ccda::SFTP_DROPBOX,
                    'imported' => false,
                    'xml'      => trim($ccdaString),
                ]);

                $count++;
            }
        }

        \Log::info("Finished Splitting $this->fileName! $count CCDAs created.");

//        $newPath = 'done/' . str_replace('.xml', '.processed', $this->fileName);
//        \Storage::disk('ccdas')->move($this->fileName, $newPath);
    }
}

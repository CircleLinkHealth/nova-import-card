<?php

namespace App\Jobs;

use App\CLH\Repositories\CCDImporterRepository;
use App\Models\MedicalRecords\Ccda;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ConvertCcdasToJson implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    protected $ccda;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Ccda $ccda)
    {
        $this->ccda = $ccda;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $json = (new CCDImporterRepository())->toJson($this->ccda->xml);

        if (!$json) {
            throw new \Exception("Could not convert CCDA {$this->ccda->id} to json.");
        }

        $this->ccda->update([
            'json' => $json
        ]);
    }
}

<?php

namespace App\Jobs;

use App\CLH\Repositories\CCDImporterRepository;
use App\Models\MedicalRecords\Ccda;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ConvertCcdaToJson implements ShouldQueue
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
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        if (empty($this->ccda->json)) {
            $json = (new CCDImporterRepository())->toJson($this->ccda->xml);

            if (!$json) {
                throw new \Exception("Could not convert CCDA {$this->ccda->id} to json.");
            }

            $this->ccda->update([
                'json' => $json,
            ]);
        }
    }
}

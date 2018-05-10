<?php

namespace App\Jobs;

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
            $this->ccda->bluebuttonJson();
        }
    }
}

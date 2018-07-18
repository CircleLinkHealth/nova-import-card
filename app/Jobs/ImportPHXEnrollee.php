<?php

namespace App\Jobs;

use App\Enrollee;
use App\Services\MedicalRecords\ImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportPHXEnrollee implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Enrollee
     */
    private $enrollee;

    /**
     * Create a new job instance.
     *
     * @param Enrollee $enrollee
     */
    public function __construct(Enrollee $enrollee)
    {
        $this->enrollee = $enrollee;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ImportService $importService)
    {
        return $importService->importPHXEnrollee($this->enrollee);
    }
}

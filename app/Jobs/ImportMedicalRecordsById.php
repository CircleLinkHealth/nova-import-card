<?php

namespace App\Jobs;

use App\Models\MedicalRecords\Ccda;
use App\Practice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportMedicalRecordsById implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Practice
     */
    private $practice;

    /**
     * @var array
     */
    private $medicalRecordIds;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $medicalRecordIds, Practice $practice)
    {
        $this->medicalRecordIds = $medicalRecordIds;
        $this->practice         = $practice;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $imported = Ccda::withTrashed()
                        ->whereIn('id', $this->medicalRecordIds)
                        ->wherePracticeId($this->practice->id)
                        ->get()
                        ->map(function ($ccda) {
                            ImportCcda::dispatch($ccda)->onQueue('low');
                        });
    }
}

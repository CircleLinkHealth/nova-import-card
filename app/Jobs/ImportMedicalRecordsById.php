<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

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
     * @var array
     */
    private $medicalRecordIds;
    /**
     * @var Practice
     */
    private $practice;

    /**
     * Create a new job instance.
     */
    public function __construct(array $medicalRecordIds, Practice $practice)
    {
        $this->medicalRecordIds = $medicalRecordIds;
        $this->practice         = $practice;
    }

    /**
     * Execute the job.
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

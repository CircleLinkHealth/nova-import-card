<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Models\MedicalRecords\Ccda;
use App\Models\MedicalRecords\ImportedMedicalRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportCcda implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $ccda;

    /**
     * Create a new job instance.
     */
    public function __construct(Ccda $ccda)
    {
        $this->ccda = $ccda;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $importedMedicalRecord = $this->ccda->import();

        if (is_a($importedMedicalRecord, ImportedMedicalRecord::class)) {
            $this->ccda->imported = true;
            $this->ccda->status   = Ccda::QA;
            $this->ccda->save();
        }
    }
}

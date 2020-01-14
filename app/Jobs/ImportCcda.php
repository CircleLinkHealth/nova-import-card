<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use CircleLinkHealth\CarePlanModels\Entities\Ccda;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportCcda implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * @var \CircleLinkHealth\CarePlanModels\Entities\Ccda
     */
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
            $update = Ccda::whereId($this->ccda->id)
                ->update(
                    [
                        'status'   => Ccda::QA,
                        'imported' => true,
                    ]
                );
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return ['import', 'ccda:'.$this->ccda->id];
    }
}

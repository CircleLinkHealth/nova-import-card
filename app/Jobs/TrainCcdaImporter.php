<?php

namespace App\Jobs;

use App\CLH\Repositories\CCDImporterRepository;
use App\Models\MedicalRecords\Ccda;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TrainCcdaImporter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $ccda;

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

        $this->ccda->json = $json;
        $this->ccda->save();

        $importedMedicalRecord = $this->ccda->import();

        $link = link_to_route('get.importer.training.results',
            "Click to review training results for Imported Medical Record with id {$importedMedicalRecord->id}", [
                'imrId' => $importedMedicalRecord->id,
            ]);

        sendSlackMessage('#ccda-trainer', $link);
    }
}

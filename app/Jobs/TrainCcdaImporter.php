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
    private $file;
    private $authUser;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($path, User $authUser)
    {
        $this->file = file_get_contents($path);
        $this->authUser = $authUser;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $xml = $this->file;

        $json = (new CCDImporterRepository())->toJson($xml);

        $ccda = Ccda::create([
            'user_id'   => $this->authUser->id ?? null,
            'vendor_id' => 1,
            'xml'       => $xml,
            'json'      => $json,
            'source'    => Ccda::IMPORTER,
        ]);

        $importedMedicalRecord = $ccda->import();

        $link = link_to_route('get.importer.training.results',
            "Click to review training results for Imported MEdical Record with id {$importedMedicalRecord->id}", [
                'imrId' => $importedMedicalRecord->id,
            ]);

        sendSlackMessage('#ccda-trainer', $link);
    }
}

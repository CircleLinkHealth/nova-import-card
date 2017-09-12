<?php

namespace App\Jobs;

use App\CLH\Repositories\CCDImporterRepository;
use App\Models\MedicalRecords\Ccda;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;

class TrainCcdaImporter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $file;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($file)
    {
        $this->file = $file;
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
            'user_id'   => auth()->user()->id,
            'vendor_id' => 1,
            'xml'       => $xml,
            'json'      => $json,
            'source'    => Ccda::IMPORTER,
        ]);

        $importedMedicalRecord = $ccda->import();

        $link = link_to_route('get.importer.training.results', "Click to review training results for Imported MEdical Record with id {$importedMedicalRecord->id}", [
            'imrId' => $importedMedicalRecord->id
        ]);

        sendSlackMessage('#ccda-trainer', $link);
    }
}

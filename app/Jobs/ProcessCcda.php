<?php

namespace App\Jobs;

use App\CLH\Repositories\CCDImporterRepository;
use App\Importer\Loggers\Ccda\CcdToLogTranformer;
use App\Models\MedicalRecords\Ccda;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessCcda implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    public $ccdaXmlString;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($ccdaXmlString)
    {
        $this->ccdaXmlString = $ccdaXmlString;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ccda = Ccda::create([
            'source'   => Ccda::SFTP_DROPBOX,
            'imported' => false,
            'xml'      => trim($this->ccdaXmlString . '</ClinicalDocument>'),
            'status'   => Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY,
        ]);

        $ccdas[] = $ccda;

        $json = (new CCDImporterRepository())->toJson($ccda->xml);

        if ($json) {
            $ccda->json = $json;

            $decoded = json_decode($json);

            if ($decoded) {
                $ccda->mrn = $decoded->demographics->mrn_number;

                $provider = (new CcdToLogTranformer())->provider($decoded->document->documentation_of[0]);

                $ccda->referring_provider_name = "{$provider['first_name']} {$provider['last_name']}";

                $ccda->date = Carbon::parse($decoded->document->date)->toDateTimeString();
            }

            $ccda->save();
        }

        $this->handleDuplicateCcdas($ccda);
    }

    public function handleDuplicateCcdas(Ccda $ccda) {
        $duplicate = Ccda::where('mrn', '=', $ccda->mrn)->first();

        if ($duplicate) {
            if ($duplicate->date->gt($ccda->date)) {
                $ccda->delete();
            } else {
                $duplicate->delete();
            }
        }
    }
}

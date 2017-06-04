<?php

namespace App\Jobs;

use App\CLH\Repositories\CCDImporterRepository;
use App\Importer\Loggers\Ccda\CcdToLogTranformer;
use App\Models\MedicalRecords\Ccda;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCcda implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    public $ccda;

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
        $ccda = $this->ccda;

        $ccdas[] = $ccda;

        $json = (new CCDImporterRepository())->toJson($ccda->xml);

        if ($json) {
            $ccda->json = $json;

            $decoded = json_decode($json);

            if ($decoded) {
                $ccda->mrn = $decoded->demographics->mrn_number;

                if (array_key_exists(0, $decoded->document->documentation_of)) {
                    $provider = (new CcdToLogTranformer())->provider($decoded->document->documentation_of[0]);
                    $ccda->referring_provider_name = "{$provider['first_name']} {$provider['last_name']}";
                }

                $ccda->date = Carbon::parse($decoded->document->date)->toDateTimeString();
            }

            $ccda->save();
        }

        $this->handleDuplicateCcdas($ccda);
    }

    public function handleDuplicateCcdas(Ccda $ccda)
    {
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

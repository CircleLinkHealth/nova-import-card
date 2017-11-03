<?php

namespace App\Jobs;

use App\AppConfig;
use App\CLH\Repositories\CCDImporterRepository;
use App\Enrollee;
use App\Importer\Loggers\Ccda\CcdToLogTranformer;
use App\Models\MedicalRecords\Ccda;
use App\Models\PatientData\LGH\LGHProvider;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCcdaLGHMixup implements ShouldQueue
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
        $this->ccda = Ccda::withTrashed()
            ->find($ccda->id);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ccda = $this->ccda;

        $json = $ccda->bluebuttonJson();

        if ($json) {
            if ($json) {
                $ccda->mrn = $json->demographics->mrn_number;

                if (array_key_exists(0, $json->document->documentation_of)) {
                    $provider = (new CcdToLogTranformer())->provider($json->document->documentation_of[0]);
                    $ccda->referring_provider_name = "{$provider['first_name']} {$provider['last_name']}";
                }

                $ccda->date = Carbon::parse($json->document->date)->toDateTimeString();
            }

            $ccda->save();
        }

        $this->updateEnrollee($ccda);
    }

    public function updateEnrollee(Ccda $ccda)
    {
        $enrollee = Enrollee::whereMedicalRecordType(Ccda::class)
            ->whereMedicalRecordId($ccda->id)
            ->first();

        if (!$enrollee) {
            return;
        }

        $enrollee->mrn = $ccda->mrn;

        $lghProvider = LGHProvider::whereMrn($ccda->mrn)
            ->first();

        if ($lghProvider) {
            $enrollee->referring_provider_name = $lghProvider->att_phys;

            $lghProvider->medical_record_type = $enrollee->medical_record_type;
            $lghProvider->medical_record_id = $enrollee->medical_record_id;
            $lghProvider->save();
        }

        $enrollee->save();
    }
}
